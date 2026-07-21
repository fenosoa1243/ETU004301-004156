-- Base finale unifiée : schéma de base + extension V2 (opérateurs externes, commissions, vues et triggers)
PRAGMA foreign_keys = ON;

-- ============================================================
-- 1. TABLES DE BASE
-- ============================================================
CREATE TABLE IF NOT EXISTS prefixes (
    id          INTEGER PRIMARY KEY AUTOINCREMENT,
    prefix      TEXT NOT NULL UNIQUE,
    actif       INTEGER NOT NULL DEFAULT 1 CHECK (actif IN (0, 1)),
    created_at  TEXT NOT NULL DEFAULT (datetime('now')),
    CHECK (length(prefix) = 3)
);

CREATE TABLE IF NOT EXISTS clients (
    id          INTEGER PRIMARY KEY AUTOINCREMENT,
    telephone   TEXT NOT NULL UNIQUE,
    nom         TEXT,
    solde       REAL NOT NULL DEFAULT 0 CHECK (solde >= 0),
    created_at  TEXT NOT NULL DEFAULT (datetime('now')),
    CHECK (length(telephone) = 10)
);

CREATE TABLE IF NOT EXISTS operation_types (
    id              INTEGER PRIMARY KEY AUTOINCREMENT,
    nom             TEXT NOT NULL UNIQUE,
    description     TEXT,
    applique_frais  INTEGER NOT NULL DEFAULT 0 CHECK (applique_frais IN (0, 1)),
    created_at      TEXT NOT NULL DEFAULT (datetime('now'))
);

CREATE TABLE IF NOT EXISTS fee_scales (
    id                  INTEGER PRIMARY KEY AUTOINCREMENT,
    operation_type_id   INTEGER NOT NULL,
    montant_min         REAL NOT NULL CHECK (montant_min >= 0),
    montant_max         REAL NOT NULL CHECK (montant_max > 0),
    frais               REAL NOT NULL CHECK (frais >= 0),
    created_at          TEXT NOT NULL DEFAULT (datetime('now')),
    CHECK (montant_max >= montant_min),
    FOREIGN KEY (operation_type_id) REFERENCES operation_types (id) ON DELETE CASCADE ON UPDATE CASCADE
);

-- ============================================================
-- 2. EXTENSION V2 : OPÉRATEURS EXTERNES ET COMMISSIONS
-- ============================================================
CREATE TABLE IF NOT EXISTS operators (
    id          INTEGER PRIMARY KEY AUTOINCREMENT,
    nom         TEXT NOT NULL UNIQUE,
    code        TEXT NOT NULL UNIQUE,
    actif       INTEGER NOT NULL DEFAULT 1 CHECK (actif IN (0, 1)),
    created_at  TEXT NOT NULL DEFAULT (datetime('now'))
);

CREATE TABLE IF NOT EXISTS operator_prefixes (
    id          INTEGER PRIMARY KEY AUTOINCREMENT,
    operator_id INTEGER NOT NULL,
    prefix      TEXT NOT NULL UNIQUE,
    actif       INTEGER NOT NULL DEFAULT 1 CHECK (actif IN (0, 1)),
    created_at  TEXT NOT NULL DEFAULT (datetime('now')),
    CHECK (length(prefix) = 3),
    FOREIGN KEY (operator_id) REFERENCES operators (id) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE IF NOT EXISTS inter_operator_commissions (
    id          INTEGER PRIMARY KEY AUTOINCREMENT,
    pourcentage REAL NOT NULL CHECK (pourcentage >= 0 AND pourcentage <= 100),
    actif       INTEGER NOT NULL DEFAULT 1 CHECK (actif IN (0, 1)),
    created_at  TEXT NOT NULL DEFAULT (datetime('now'))
);

CREATE TABLE IF NOT EXISTS commission_history (
    id                  INTEGER PRIMARY KEY AUTOINCREMENT,
    commission_id       INTEGER NOT NULL,
    pourcentage_avant   REAL NOT NULL,
    pourcentage_apres   REAL NOT NULL,
    created_at          TEXT NOT NULL DEFAULT (datetime('now')),
    FOREIGN KEY (commission_id) REFERENCES inter_operator_commissions (id) ON DELETE CASCADE ON UPDATE CASCADE
);

-- ============================================================
-- 3. TRANSACTIONS FINALES
-- ============================================================
CREATE TABLE IF NOT EXISTS transactions (
    id                        INTEGER PRIMARY KEY AUTOINCREMENT,
    reference                 TEXT NOT NULL UNIQUE,
    operation_type_id         INTEGER NOT NULL,
    client_source_id          INTEGER NOT NULL,
    client_destination_id     INTEGER,
    destination_telephone     TEXT,
    montant                   REAL NOT NULL CHECK (montant > 0),
    frais                     REAL NOT NULL DEFAULT 0 CHECK (frais >= 0),
    frais_retrait             REAL NOT NULL DEFAULT 0 CHECK (frais_retrait >= 0),
    commission_supplementaire REAL NOT NULL DEFAULT 0 CHECK (commission_supplementaire >= 0),
    montant_total             REAL NOT NULL CHECK (montant_total > 0),
    is_external               INTEGER NOT NULL DEFAULT 0 CHECK (is_external IN (0, 1)),
    external_operator_id      INTEGER,
    batch_reference           TEXT,
    solde_avant               REAL NOT NULL CHECK (solde_avant >= 0),
    solde_apres               REAL NOT NULL CHECK (solde_apres >= 0),
    created_at                TEXT NOT NULL DEFAULT (datetime('now')),
    CHECK (montant_total = montant + frais + frais_retrait + commission_supplementaire),
    CHECK (client_destination_id IS NULL OR client_destination_id <> client_source_id),
    FOREIGN KEY (operation_type_id) REFERENCES operation_types (id) ON DELETE RESTRICT ON UPDATE CASCADE,
    FOREIGN KEY (client_source_id) REFERENCES clients (id) ON DELETE RESTRICT ON UPDATE CASCADE,
    FOREIGN KEY (client_destination_id) REFERENCES clients (id) ON DELETE RESTRICT ON UPDATE CASCADE,
    FOREIGN KEY (external_operator_id) REFERENCES operators (id) ON DELETE RESTRICT ON UPDATE CASCADE
);

-- ============================================================
-- 4. INDEXES
-- ============================================================
CREATE INDEX IF NOT EXISTS idx_prefixes_actif ON prefixes (actif);
CREATE INDEX IF NOT EXISTS idx_clients_telephone ON clients (telephone);
CREATE INDEX IF NOT EXISTS idx_fee_scales_montant_range ON fee_scales (operation_type_id, montant_min, montant_max);
CREATE INDEX IF NOT EXISTS idx_operator_prefixes_operator ON operator_prefixes (operator_id);
CREATE INDEX IF NOT EXISTS idx_operator_prefixes_actif ON operator_prefixes (actif);
CREATE INDEX IF NOT EXISTS idx_transactions_reference ON transactions (reference);
CREATE INDEX IF NOT EXISTS idx_transactions_created_at ON transactions (created_at);
CREATE INDEX IF NOT EXISTS idx_transactions_external ON transactions (is_external, external_operator_id);

-- ============================================================
-- 5. VUES
-- ============================================================
DROP VIEW IF EXISTS vue_historique;
CREATE VIEW vue_historique AS
SELECT
    t.id AS transaction_id,
    t.reference,
    ot.nom AS type_operation,
    cs.telephone AS expediteur,
    COALESCE(cd.telephone, t.destination_telephone) AS destinataire,
    t.montant,
    t.frais,
    t.frais_retrait,
    t.commission_supplementaire,
    t.montant_total,
    t.is_external,
    t.batch_reference,
    t.solde_avant,
    t.solde_apres,
    t.created_at AS date_operation,
    o.nom AS operateur_externe
FROM transactions t
JOIN operation_types ot ON ot.id = t.operation_type_id
JOIN clients cs ON cs.id = t.client_source_id
LEFT JOIN clients cd ON cd.id = t.client_destination_id
LEFT JOIN operators o ON o.id = t.external_operator_id;

DROP VIEW IF EXISTS vue_revenus_operateur;
CREATE VIEW vue_revenus_operateur AS
SELECT
    ot.id AS operation_type_id,
    ot.nom AS type_operation,
    COUNT(t.id) AS nombre_operations,
    COALESCE(SUM(t.frais), 0) AS total_frais,
    COALESCE(SUM(t.commission_supplementaire), 0) AS total_commission_sup
FROM operation_types ot
LEFT JOIN transactions t ON t.operation_type_id = ot.id
GROUP BY ot.id, ot.nom;

DROP VIEW IF EXISTS vue_gains_internes;
CREATE VIEW vue_gains_internes AS
SELECT
    COUNT(*) AS nb_transferts,
    COALESCE(SUM(frais), 0) AS total_frais,
    COALESCE(SUM(montant), 0) AS total_montant
FROM transactions
WHERE operation_type_id = 3 AND is_external = 0;

DROP VIEW IF EXISTS vue_gains_externes;
CREATE VIEW vue_gains_externes AS
SELECT
    COUNT(*) AS nb_transferts,
    COALESCE(SUM(frais), 0) AS total_frais,
    COALESCE(SUM(commission_supplementaire), 0) AS total_commission_sup,
    COALESCE(SUM(frais + commission_supplementaire), 0) AS total_gains,
    COALESCE(SUM(montant), 0) AS total_montant
FROM transactions
WHERE operation_type_id = 3 AND is_external = 1;

DROP VIEW IF EXISTS vue_montants_a_envoyer;
CREATE VIEW vue_montants_a_envoyer AS
SELECT
    o.id AS operator_id,
    o.nom AS operateur,
    COUNT(t.id) AS nb_transferts,
    COALESCE(SUM(t.montant), 0) AS montant_total,
    COALESCE(SUM(t.frais + t.commission_supplementaire), 0) AS commission_percue,
    COALESCE(SUM(t.montant), 0) AS montant_net_a_envoyer,
    MAX(t.created_at) AS derniere_date
FROM operators o
LEFT JOIN transactions t ON t.external_operator_id = o.id AND t.is_external = 1 AND t.operation_type_id = 3
GROUP BY o.id, o.nom;

DROP VIEW IF EXISTS vue_solde_clients;
CREATE VIEW vue_solde_clients AS
SELECT id AS client_id, telephone AS numero, nom, solde FROM clients;

-- ============================================================
-- 6. TRIGGERS
-- ============================================================
DROP TRIGGER IF EXISTS tg_clients_insert_prefix_check;
CREATE TRIGGER tg_clients_insert_prefix_check
BEFORE INSERT ON clients
BEGIN
    SELECT CASE
        WHEN NOT EXISTS (SELECT 1 FROM prefixes WHERE prefix = substr(NEW.telephone, 1, 3) AND actif = 1)
        THEN RAISE(ABORT, 'Numéro invalide : Le préfixe n''est pas supporté par l''opérateur.')
    END;
END;

DROP TRIGGER IF EXISTS tg_transactions_update_soldes;
CREATE TRIGGER tg_transactions_update_soldes
AFTER INSERT ON transactions
BEGIN
    UPDATE clients
    SET solde = solde + NEW.montant
    WHERE NEW.operation_type_id = 1
      AND id = NEW.client_source_id;

    UPDATE clients
    SET solde = solde - NEW.montant_total
    WHERE NEW.operation_type_id IN (2, 3)
      AND id = NEW.client_source_id;

    UPDATE clients
    SET solde = solde + NEW.montant
    WHERE NEW.operation_type_id = 3
      AND NEW.is_external = 0
      AND NEW.client_destination_id IS NOT NULL
      AND id = NEW.client_destination_id;
END;

-- ============================================================
-- 7. DONNÉES INITIALES
-- ============================================================
INSERT OR IGNORE INTO prefixes (prefix, actif) VALUES ('030', 1), ('039', 1);

INSERT OR IGNORE INTO operation_types (nom, description, applique_frais) VALUES
    ('Dépôt', 'Ajout d''argent sur le compte du client, sans frais.', 0),
    ('Retrait', 'Retrait d''argent du compte du client, avec frais.', 1),
    ('Transfert', 'Envoi d''argent vers un autre client, avec frais.', 1);

INSERT INTO fee_scales (operation_type_id, montant_min, montant_max, frais)
SELECT id, 0, 99, 0
FROM operation_types
WHERE nom = 'Retrait'
  AND NOT EXISTS (
      SELECT 1 FROM fee_scales fs
      WHERE fs.operation_type_id = operation_types.id
        AND fs.montant_min = 0
        AND fs.montant_max = 99
        AND fs.frais = 0
  );

INSERT INTO fee_scales (operation_type_id, montant_min, montant_max, frais)
SELECT id, 100, 1000, 50
FROM operation_types
WHERE nom = 'Retrait'
  AND NOT EXISTS (
      SELECT 1 FROM fee_scales fs
      WHERE fs.operation_type_id = operation_types.id
        AND fs.montant_min = 100
        AND fs.montant_max = 1000
        AND fs.frais = 50
  );

INSERT INTO fee_scales (operation_type_id, montant_min, montant_max, frais)
SELECT id, 1001, 5000, 50
FROM operation_types
WHERE nom = 'Retrait'
  AND NOT EXISTS (
      SELECT 1 FROM fee_scales fs
      WHERE fs.operation_type_id = operation_types.id
        AND fs.montant_min = 1001
        AND fs.montant_max = 5000
        AND fs.frais = 50
  );

INSERT INTO fee_scales (operation_type_id, montant_min, montant_max, frais)
SELECT id, 5001, 10000, 100
FROM operation_types
WHERE nom = 'Retrait'
  AND NOT EXISTS (
      SELECT 1 FROM fee_scales fs
      WHERE fs.operation_type_id = operation_types.id
        AND fs.montant_min = 5001
        AND fs.montant_max = 10000
        AND fs.frais = 100
  );

INSERT INTO fee_scales (operation_type_id, montant_min, montant_max, frais)
SELECT id, 10001, 25000, 200
FROM operation_types
WHERE nom = 'Retrait'
  AND NOT EXISTS (
      SELECT 1 FROM fee_scales fs
      WHERE fs.operation_type_id = operation_types.id
        AND fs.montant_min = 10001
        AND fs.montant_max = 25000
        AND fs.frais = 200
  );

INSERT INTO fee_scales (operation_type_id, montant_min, montant_max, frais)
SELECT id, 25001, 50000, 400
FROM operation_types
WHERE nom = 'Retrait'
  AND NOT EXISTS (
      SELECT 1 FROM fee_scales fs
      WHERE fs.operation_type_id = operation_types.id
        AND fs.montant_min = 25001
        AND fs.montant_max = 50000
        AND fs.frais = 400
  );

INSERT INTO fee_scales (operation_type_id, montant_min, montant_max, frais)
SELECT id, 50001, 100000, 800
FROM operation_types
WHERE nom = 'Retrait'
  AND NOT EXISTS (
      SELECT 1 FROM fee_scales fs
      WHERE fs.operation_type_id = operation_types.id
        AND fs.montant_min = 50001
        AND fs.montant_max = 100000
        AND fs.frais = 800
  );

INSERT INTO fee_scales (operation_type_id, montant_min, montant_max, frais)
SELECT id, 100001, 250000, 1500
FROM operation_types
WHERE nom = 'Retrait'
  AND NOT EXISTS (
      SELECT 1 FROM fee_scales fs
      WHERE fs.operation_type_id = operation_types.id
        AND fs.montant_min = 100001
        AND fs.montant_max = 250000
        AND fs.frais = 1500
  );

INSERT INTO fee_scales (operation_type_id, montant_min, montant_max, frais)
SELECT id, 250001, 500000, 1500
FROM operation_types
WHERE nom = 'Retrait'
  AND NOT EXISTS (
      SELECT 1 FROM fee_scales fs
      WHERE fs.operation_type_id = operation_types.id
        AND fs.montant_min = 250001
        AND fs.montant_max = 500000
        AND fs.frais = 1500
  );

INSERT INTO fee_scales (operation_type_id, montant_min, montant_max, frais)
SELECT id, 500001, 1000000, 2500
FROM operation_types
WHERE nom = 'Retrait'
  AND NOT EXISTS (
      SELECT 1 FROM fee_scales fs
      WHERE fs.operation_type_id = operation_types.id
        AND fs.montant_min = 500001
        AND fs.montant_max = 1000000
        AND fs.frais = 2500
  );

INSERT INTO fee_scales (operation_type_id, montant_min, montant_max, frais)
SELECT id, 1000001, 2000000, 3000
FROM operation_types
WHERE nom = 'Retrait'
  AND NOT EXISTS (
      SELECT 1 FROM fee_scales fs
      WHERE fs.operation_type_id = operation_types.id
        AND fs.montant_min = 1000001
        AND fs.montant_max = 2000000
        AND fs.frais = 3000
  );

INSERT INTO fee_scales (operation_type_id, montant_min, montant_max, frais)
SELECT id, 0, 99, 0
FROM operation_types
WHERE nom = 'Transfert'
  AND NOT EXISTS (
      SELECT 1 FROM fee_scales fs
      WHERE fs.operation_type_id = operation_types.id
        AND fs.montant_min = 0
        AND fs.montant_max = 99
        AND fs.frais = 0
  );

INSERT INTO fee_scales (operation_type_id, montant_min, montant_max, frais)
SELECT id, 100, 1000, 50
FROM operation_types
WHERE nom = 'Transfert'
  AND NOT EXISTS (
      SELECT 1 FROM fee_scales fs
      WHERE fs.operation_type_id = operation_types.id
        AND fs.montant_min = 100
        AND fs.montant_max = 1000
        AND fs.frais = 50
  );

INSERT INTO fee_scales (operation_type_id, montant_min, montant_max, frais)
SELECT id, 1001, 5000, 50
FROM operation_types
WHERE nom = 'Transfert'
  AND NOT EXISTS (
      SELECT 1 FROM fee_scales fs
      WHERE fs.operation_type_id = operation_types.id
        AND fs.montant_min = 1001
        AND fs.montant_max = 5000
        AND fs.frais = 50
  );

INSERT INTO fee_scales (operation_type_id, montant_min, montant_max, frais)
SELECT id, 5001, 10000, 100
FROM operation_types
WHERE nom = 'Transfert'
  AND NOT EXISTS (
      SELECT 1 FROM fee_scales fs
      WHERE fs.operation_type_id = operation_types.id
        AND fs.montant_min = 5001
        AND fs.montant_max = 10000
        AND fs.frais = 100
  );

INSERT INTO fee_scales (operation_type_id, montant_min, montant_max, frais)
SELECT id, 10001, 25000, 200
FROM operation_types
WHERE nom = 'Transfert'
  AND NOT EXISTS (
      SELECT 1 FROM fee_scales fs
      WHERE fs.operation_type_id = operation_types.id
        AND fs.montant_min = 10001
        AND fs.montant_max = 25000
        AND fs.frais = 200
  );

INSERT INTO fee_scales (operation_type_id, montant_min, montant_max, frais)
SELECT id, 25001, 50000, 400
FROM operation_types
WHERE nom = 'Transfert'
  AND NOT EXISTS (
      SELECT 1 FROM fee_scales fs
      WHERE fs.operation_type_id = operation_types.id
        AND fs.montant_min = 25001
        AND fs.montant_max = 50000
        AND fs.frais = 400
  );

INSERT INTO fee_scales (operation_type_id, montant_min, montant_max, frais)
SELECT id, 50001, 100000, 800
FROM operation_types
WHERE nom = 'Transfert'
  AND NOT EXISTS (
      SELECT 1 FROM fee_scales fs
      WHERE fs.operation_type_id = operation_types.id
        AND fs.montant_min = 50001
        AND fs.montant_max = 100000
        AND fs.frais = 800
  );

INSERT INTO fee_scales (operation_type_id, montant_min, montant_max, frais)
SELECT id, 100001, 250000, 1500
FROM operation_types
WHERE nom = 'Transfert'
  AND NOT EXISTS (
      SELECT 1 FROM fee_scales fs
      WHERE fs.operation_type_id = operation_types.id
        AND fs.montant_min = 100001
        AND fs.montant_max = 250000
        AND fs.frais = 1500
  );

INSERT INTO fee_scales (operation_type_id, montant_min, montant_max, frais)
SELECT id, 250001, 500000, 1500
FROM operation_types
WHERE nom = 'Transfert'
  AND NOT EXISTS (
      SELECT 1 FROM fee_scales fs
      WHERE fs.operation_type_id = operation_types.id
        AND fs.montant_min = 250001
        AND fs.montant_max = 500000
        AND fs.frais = 1500
  );

INSERT INTO fee_scales (operation_type_id, montant_min, montant_max, frais)
SELECT id, 500001, 1000000, 2500
FROM operation_types
WHERE nom = 'Transfert'
  AND NOT EXISTS (
      SELECT 1 FROM fee_scales fs
      WHERE fs.operation_type_id = operation_types.id
        AND fs.montant_min = 500001
        AND fs.montant_max = 1000000
        AND fs.frais = 2500
  );

INSERT INTO fee_scales (operation_type_id, montant_min, montant_max, frais)
SELECT id, 1000001, 2000000, 3000
FROM operation_types
WHERE nom = 'Transfert'
  AND NOT EXISTS (
      SELECT 1 FROM fee_scales fs
      WHERE fs.operation_type_id = operation_types.id
        AND fs.montant_min = 1000001
        AND fs.montant_max = 2000000
        AND fs.frais = 3000
  );

INSERT OR IGNORE INTO operators (nom, code, actif) VALUES
    ('MVola', 'MVOLA', 1),
    ('Orange Money', 'ORANGE', 1),
    ('Airtel Money', 'AIRTEL', 1);

INSERT OR IGNORE INTO operator_prefixes (operator_id, prefix, actif)
SELECT id, '034', 1 FROM operators WHERE code = 'MVOLA';

INSERT OR IGNORE INTO operator_prefixes (operator_id, prefix, actif)
SELECT id, '032', 1 FROM operators WHERE code = 'ORANGE';

INSERT OR IGNORE INTO operator_prefixes (operator_id, prefix, actif)
SELECT id, '031', 1 FROM operators WHERE code = 'AIRTEL';

INSERT OR IGNORE INTO inter_operator_commissions (pourcentage, actif) VALUES (5, 1);

-- ============================================================
-- 8. DONNÉES DE DÉMONSTRATION (clients + dépôts initiaux)
-- ============================================================
INSERT OR IGNORE INTO clients (telephone, nom, solde) VALUES
    ('0391111111', 'Rakoto Jean', 0),
    ('0392222222', 'Rasoa Marie', 0),
    ('0303333333', 'Randriamampionona Paul', 0),
    ('0394444444', 'Andriamalala Nomena', 0);

INSERT OR IGNORE INTO transactions (
    reference, operation_type_id, client_source_id, client_destination_id,
    destination_telephone, montant, frais, frais_retrait, commission_supplementaire,
    montant_total, is_external, external_operator_id, batch_reference, solde_avant, solde_apres
)
SELECT 'SEED-DEP-0391111111', ot.id, c.id, NULL, NULL, 500000, 0, 0, 0, 500000, 0, NULL, NULL, 0, 500000
FROM operation_types ot, clients c
WHERE ot.nom = 'Dépôt' AND c.telephone = '0391111111';

INSERT OR IGNORE INTO transactions (
    reference, operation_type_id, client_source_id, client_destination_id,
    destination_telephone, montant, frais, frais_retrait, commission_supplementaire,
    montant_total, is_external, external_operator_id, batch_reference, solde_avant, solde_apres
)
SELECT 'SEED-DEP-0392222222', ot.id, c.id, NULL, NULL, 250000, 0, 0, 0, 250000, 0, NULL, NULL, 0, 250000
FROM operation_types ot, clients c
WHERE ot.nom = 'Dépôt' AND c.telephone = '0392222222';

INSERT OR IGNORE INTO transactions (
    reference, operation_type_id, client_source_id, client_destination_id,
    destination_telephone, montant, frais, frais_retrait, commission_supplementaire,
    montant_total, is_external, external_operator_id, batch_reference, solde_avant, solde_apres
)
SELECT 'SEED-DEP-0303333333', ot.id, c.id, NULL, NULL, 100000, 0, 0, 0, 100000, 0, NULL, NULL, 0, 100000
FROM operation_types ot, clients c
WHERE ot.nom = 'Dépôt' AND c.telephone = '0303333333';


CREATE TABLE internal_promotions (
    id          INTEGER PRIMARY KEY AUTOINCREMENT,
    pourcentage REAL NOT NULL CHECK (pourcentage >= 0 AND pourcentage <= 100),
    actif       INTEGER NOT NULL DEFAULT 1 CHECK (actif IN (0, 1)),
    created_at  TEXT NOT NULL DEFAULT (datetime('now'))
);


CREATE TABLE promotion_history (
    id                  INTEGER PRIMARY KEY AUTOINCREMENT,
    promotion_id        INTEGER NOT NULL,
    pourcentage_avant   REAL NOT NULL,
    pourcentage_apres   REAL NOT NULL,
    created_at          TEXT NOT NULL DEFAULT (datetime('now')),

    FOREIGN KEY (promotion_id)
        REFERENCES internal_promotions (id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
);

INSERT INTO internal_promotions (pourcentage, actif) VALUES (10, 1);