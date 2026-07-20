-- prefixes
CREATE TABLE prefixes (
    id          INTEGER PRIMARY KEY AUTOINCREMENT,
    prefix      TEXT NOT NULL UNIQUE,
    actif       INTEGER NOT NULL DEFAULT 1 CHECK (actif IN (0, 1)),
    created_at  TEXT NOT NULL DEFAULT (datetime('now')),

    CHECK (length(prefix) = 3)
);

-- clients
CREATE TABLE clients (
    id          INTEGER PRIMARY KEY AUTOINCREMENT,
    telephone   TEXT NOT NULL UNIQUE,
    nom         TEXT,
    solde       REAL NOT NULL DEFAULT 0 CHECK (solde >= 0),
    created_at  TEXT NOT NULL DEFAULT (datetime('now'))
);

-- operation_types
CREATE TABLE operation_types (
    id              INTEGER PRIMARY KEY AUTOINCREMENT,
    nom             TEXT NOT NULL UNIQUE,
    description     TEXT,
    applique_frais  INTEGER NOT NULL DEFAULT 0 CHECK (applique_frais IN (0, 1)),
    created_at      TEXT NOT NULL DEFAULT (datetime('now'))
);

-- fee_scales
CREATE TABLE fee_scales (
    id                  INTEGER PRIMARY KEY AUTOINCREMENT,
    operation_type_id   INTEGER NOT NULL,
    montant_min         REAL NOT NULL CHECK (montant_min > 0),
    montant_max         REAL NOT NULL CHECK (montant_max > 0),
    frais               REAL NOT NULL CHECK (frais >= 0),
    created_at          TEXT NOT NULL DEFAULT (datetime('now')),

    CHECK (montant_max >= montant_min),

    FOREIGN KEY (operation_type_id)
        REFERENCES operation_types (id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
);

-- transactions
CREATE TABLE transactions (
    id                      INTEGER PRIMARY KEY AUTOINCREMENT,
    reference               TEXT NOT NULL UNIQUE,
    operation_type_id       INTEGER NOT NULL,
    client_source_id        INTEGER NOT NULL,
    client_destination_id   INTEGER,
    montant                 REAL NOT NULL CHECK (montant > 0),
    frais                   REAL NOT NULL DEFAULT 0 CHECK (frais >= 0),
    montant_total           REAL NOT NULL CHECK (montant_total > 0),
    solde_avant             REAL NOT NULL CHECK (solde_avant >= 0),
    solde_apres             REAL NOT NULL CHECK (solde_apres >= 0),
    created_at              TEXT NOT NULL DEFAULT (datetime('now')),

    CHECK (montant_total = montant + frais),
    CHECK (client_destination_id IS NULL OR client_destination_id <> client_source_id),

    FOREIGN KEY (operation_type_id)
        REFERENCES operation_types (id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE,

    FOREIGN KEY (client_source_id)
        REFERENCES clients (id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE,

    FOREIGN KEY (client_destination_id)
        REFERENCES clients (id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE
);

-- index
CREATE INDEX idx_prefixes_actif ON prefixes (actif);
CREATE INDEX idx_clients_telephone ON clients (telephone);
CREATE INDEX idx_fee_scales_operation_type ON fee_scales (operation_type_id);
CREATE INDEX idx_fee_scales_montant_range ON fee_scales (operation_type_id, montant_min, montant_max);
CREATE INDEX idx_transactions_reference ON transactions (reference);
CREATE INDEX idx_transactions_operation_type ON transactions (operation_type_id);
CREATE INDEX idx_transactions_client_source ON transactions (client_source_id);
CREATE INDEX idx_transactions_client_destination ON transactions (client_destination_id);
CREATE INDEX idx_transactions_created_at ON transactions (created_at);

-- vue_solde_clients
CREATE VIEW vue_solde_clients AS
SELECT
    c.id            AS client_id,
    c.telephone     AS numero,
    c.nom           AS nom,
    c.solde         AS solde
FROM clients c;

-- vue_historique
CREATE VIEW vue_historique AS
SELECT
    t.id                        AS transaction_id,
    t.reference                 AS reference,
    ot.nom                      AS type_operation,
    cs.telephone                AS expediteur,
    cd.telephone                AS destinataire,
    t.montant                   AS montant,
    t.frais                     AS frais,
    t.montant_total             AS montant_total,
    t.solde_avant               AS solde_avant,
    t.solde_apres               AS solde_apres,
    t.created_at                AS date_operation
FROM transactions t
JOIN operation_types ot ON ot.id = t.operation_type_id
JOIN clients cs         ON cs.id = t.client_source_id
LEFT JOIN clients cd    ON cd.id = t.client_destination_id;

-- vue_revenus_operateur
CREATE VIEW vue_revenus_operateur AS
SELECT
    ot.id                        AS operation_type_id,
    ot.nom                       AS type_operation,
    COUNT(t.id)                  AS nombre_operations,
    COALESCE(SUM(t.frais), 0)    AS total_frais
FROM operation_types ot
LEFT JOIN transactions t ON t.operation_type_id = ot.id
GROUP BY ot.id, ot.nom;

-- prefixes
INSERT INTO prefixes (prefix, actif) VALUES
    ('032', 1),
    ('033', 1),
    ('034', 1),
    ('037', 1),
    ('038', 1);

-- operation_types
INSERT INTO operation_types (nom, description, applique_frais) VALUES
    ('Dépôt',     'Ajout d''argent sur le compte du client, sans frais.', 0),
    ('Retrait',   'Retrait d''argent du compte du client, avec frais.', 1),
    ('Transfert', 'Envoi d''argent vers un autre client, avec frais.', 1);

-- fee_scales (Retrait)
INSERT INTO fee_scales (operation_type_id, montant_min, montant_max, frais) VALUES
    (2, 100,      1000,     50),
    (2, 1001,     5000,     50),
    (2, 5001,     10000,    100),
    (2, 10001,    25000,    200),
    (2, 25001,    50000,    400),
    (2, 50001,    100000,   800),
    (2, 100001,   250000,   1500),
    (2, 250001,   500000,   1500),
    (2, 500001,   1000000,  2500),
    (2, 1000001,  2000000,  3000);

-- fee_scales (Transfert)
INSERT INTO fee_scales (operation_type_id, montant_min, montant_max, frais) VALUES
    (3, 100,      1000,     50),
    (3, 1001,     5000,     50),
    (3, 5001,     10000,    100),
    (3, 10001,    25000,    200),
    (3, 25001,    50000,    400),
    (3, 50001,    100000,   800),
    (3, 100001,   250000,   1500),
    (3, 250001,   500000,   1500),
    (3, 500001,   1000000,  2500),
    (3, 1000001,  2000000,  3000);
