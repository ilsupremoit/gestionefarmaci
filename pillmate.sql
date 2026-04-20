-- Merged PillMate schema
-- Combines:
-- 1) domain tables from pillmate.sql
-- 2) Laravel infrastructure tables from pillmate (2).sql
-- 3) MQTT/IoT tables and extra device/assumption fields

DROP DATABASE IF EXISTS pillmate;
CREATE DATABASE pillmate CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE pillmate;

SET FOREIGN_KEY_CHECKS = 0;

-- =========================================================
-- Laravel/auth core
-- =========================================================

CREATE TABLE users (
                       id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                       nome VARCHAR(50) NOT NULL,
                       cognome VARCHAR(50) NOT NULL,
                       username VARCHAR(50) DEFAULT NULL,
                       email VARCHAR(100) DEFAULT NULL,
                       email_verified_at TIMESTAMP NULL DEFAULT NULL,
                       password VARCHAR(255) NOT NULL,
                       must_change_password TINYINT(1) NOT NULL DEFAULT 1,
                       ruolo ENUM('paziente','medico','familiare','admin') NOT NULL,
                       telefono VARCHAR(20) DEFAULT NULL,
                       remember_token VARCHAR(100) DEFAULT NULL,
                       created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
                       updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                       PRIMARY KEY (id),
                       UNIQUE KEY users_email_unique (email),
                       UNIQUE KEY users_username_unique (username)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE password_reset_tokens (
                                       email VARCHAR(255) NOT NULL,
                                       token VARCHAR(255) NOT NULL,
                                       created_at TIMESTAMP NULL DEFAULT NULL,
                                       PRIMARY KEY (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE sessions (
                          id VARCHAR(255) NOT NULL,
                          user_id BIGINT(20) UNSIGNED DEFAULT NULL,
                          ip_address VARCHAR(45) DEFAULT NULL,
                          user_agent TEXT DEFAULT NULL,
                          payload LONGTEXT NOT NULL,
                          last_activity INT(11) NOT NULL,
                          PRIMARY KEY (id),
                          KEY sessions_user_id_index (user_id),
                          KEY sessions_last_activity_index (last_activity)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE cache (
                       `key` VARCHAR(255) NOT NULL,
                       `value` MEDIUMTEXT NOT NULL,
                       expiration BIGINT(20) NOT NULL,
                       PRIMARY KEY (`key`),
                       KEY cache_expiration_index (expiration)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE cache_locks (
                             `key` VARCHAR(255) NOT NULL,
                             owner VARCHAR(255) NOT NULL,
                             expiration BIGINT(20) NOT NULL,
                             PRIMARY KEY (`key`),
                             KEY cache_locks_expiration_index (expiration)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE jobs (
                      id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                      queue VARCHAR(255) NOT NULL,
                      payload LONGTEXT NOT NULL,
                      attempts TINYINT(3) UNSIGNED NOT NULL,
                      reserved_at INT(10) UNSIGNED DEFAULT NULL,
                      available_at INT(10) UNSIGNED NOT NULL,
                      created_at INT(10) UNSIGNED NOT NULL,
                      PRIMARY KEY (id),
                      KEY jobs_queue_index (queue)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE job_batches (
                             id VARCHAR(255) NOT NULL,
                             name VARCHAR(255) NOT NULL,
                             total_jobs INT(11) NOT NULL,
                             pending_jobs INT(11) NOT NULL,
                             failed_jobs INT(11) NOT NULL,
                             failed_job_ids LONGTEXT NOT NULL,
                             options MEDIUMTEXT DEFAULT NULL,
                             cancelled_at INT(11) DEFAULT NULL,
                             created_at INT(11) NOT NULL,
                             finished_at INT(11) DEFAULT NULL,
                             PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE failed_jobs (
                             id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                             uuid VARCHAR(255) NOT NULL,
                             connection TEXT NOT NULL,
                             queue TEXT NOT NULL,
                             payload LONGTEXT NOT NULL,
                             exception LONGTEXT NOT NULL,
                             failed_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                             PRIMARY KEY (id),
                             UNIQUE KEY failed_jobs_uuid_unique (uuid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE migrations (
                            id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                            migration VARCHAR(255) NOT NULL,
                            batch INT(11) NOT NULL,
                            PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================================
-- Domain tables
-- =========================================================

CREATE TABLE pazienti (
                          id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                          id_utente BIGINT(20) UNSIGNED NOT NULL,
                          data_nascita DATE DEFAULT NULL,
                          indirizzo VARCHAR(150) DEFAULT NULL,
                          note_mediche TEXT DEFAULT NULL,
                          PRIMARY KEY (id),
                          UNIQUE KEY pazienti_id_utente_unique (id_utente)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE farmaci (
                         id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                         nome VARCHAR(100) NOT NULL,
                         descrizione TEXT DEFAULT NULL,
                         dose VARCHAR(50) DEFAULT NULL,
                         note TEXT DEFAULT NULL,
                         PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE dispositivi (
                             id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                             codice_seriale VARCHAR(100) NOT NULL,
                             id_paziente BIGINT(20) UNSIGNED NOT NULL,
                             nome_dispositivo VARCHAR(50) DEFAULT NULL,
                             stato ENUM('attivo','offline','manutenzione') NOT NULL DEFAULT 'attivo',
                             ultima_connessione DATETIME DEFAULT NULL,
                             batteria INT(11) DEFAULT NULL,
                             temperatura DECIMAL(5,2) DEFAULT NULL,
                             umidita DECIMAL(5,2) DEFAULT NULL,
                             wifi_rssi INT(11) DEFAULT NULL,
                             allarme_attivo TINYINT(1) NOT NULL DEFAULT 0,
                             scomparto_attuale INT(11) DEFAULT NULL,
                             sveglia_impostata TIME DEFAULT NULL,
                             ultimo_payload_at DATETIME DEFAULT NULL,
                             PRIMARY KEY (id),
                             UNIQUE KEY dispositivi_codice_seriale_unique (codice_seriale),
                             KEY dispositivi_id_paziente_foreign (id_paziente)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE terapie (
                         id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                         id_paziente BIGINT(20) UNSIGNED NOT NULL,
                         id_medico BIGINT(20) UNSIGNED NOT NULL,
                         id_farmaco BIGINT(20) UNSIGNED NOT NULL,
                         data_inizio DATE NOT NULL,
                         data_fine DATE DEFAULT NULL,
                         frequenza VARCHAR(50) DEFAULT NULL,
                         quantita INT(11) NOT NULL,
                         istruzioni TEXT DEFAULT NULL,
                         attiva TINYINT(1) NOT NULL DEFAULT 1,
                         PRIMARY KEY (id),
                         KEY terapie_id_paziente_foreign (id_paziente),
                         KEY terapie_id_medico_foreign (id_medico),
                         KEY terapie_id_farmaco_foreign (id_farmaco)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE somministrazioni (
                                  id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                                  id_terapia BIGINT(20) UNSIGNED NOT NULL,
                                  ora TIME NOT NULL,
                                  giorno_settimana ENUM('Lun','Mar','Mer','Gio','Ven','Sab','Dom','Tutti') NOT NULL DEFAULT 'Tutti',
                                  PRIMARY KEY (id),
                                  KEY somministrazioni_id_terapia_foreign (id_terapia)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE assunzioni (
                            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                            id_somministrazione BIGINT(20) UNSIGNED NOT NULL,
                            id_dispositivo BIGINT(20) UNSIGNED DEFAULT NULL,
                            data_prevista DATETIME NOT NULL,
                            data_erogazione DATETIME DEFAULT NULL,
                            data_conferma DATETIME DEFAULT NULL,
                            stato ENUM(
    'in_attesa','erogata','assunta','saltata','ritardo',
    'allarme_attivo','apertura_forzata','non_ritirata'
  ) NOT NULL DEFAULT 'in_attesa',
                            confermata_da ENUM('paziente','sensore','familiare','sistema') NOT NULL DEFAULT 'sistema',
                            allarme_inviato TINYINT(1) NOT NULL DEFAULT 0,
                            data_allarme DATETIME DEFAULT NULL,
                            apertura_forzata TINYINT(1) NOT NULL DEFAULT 0,
                            data_apertura_forzata DATETIME DEFAULT NULL,
                            note_evento TEXT DEFAULT NULL,
                            scomparto_numero INT(11) DEFAULT NULL,
                            PRIMARY KEY (id),
                            KEY assunzioni_id_somministrazione_foreign (id_somministrazione),
                            KEY idx_assunzioni_dispositivo (id_dispositivo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE notifiche (
                           id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                           id_utente BIGINT(20) UNSIGNED NOT NULL,
                           id_paziente BIGINT(20) UNSIGNED DEFAULT NULL,
                           id_dispositivo BIGINT(20) UNSIGNED DEFAULT NULL,
                           id_assunzione BIGINT(20) UNSIGNED DEFAULT NULL,
                           titolo VARCHAR(100) NOT NULL,
                           messaggio TEXT NOT NULL,
                           tipo ENUM('promemoria','allarme','errore','info') NOT NULL DEFAULT 'info',
                           letta TINYINT(1) NOT NULL DEFAULT 0,
                           data_invio DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                           PRIMARY KEY (id),
                           KEY notifiche_id_utente_foreign (id_utente),
                           KEY idx_notifiche_paziente (id_paziente),
                           KEY idx_notifiche_dispositivo (id_dispositivo),
                           KEY idx_notifiche_assunzione (id_assunzione)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE feedback (
                          id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                          id_paziente BIGINT(20) UNSIGNED NOT NULL,
                          id_assunzione BIGINT(20) UNSIGNED DEFAULT NULL,
                          messaggio TEXT NOT NULL,
                          stato_salute VARCHAR(100) DEFAULT NULL,
                          data_feedback DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                          PRIMARY KEY (id),
                          KEY feedback_id_paziente_foreign (id_paziente),
                          KEY feedback_id_assunzione_foreign (id_assunzione)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE medici_pazienti (
                                 id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                                 id_medico BIGINT(20) UNSIGNED NOT NULL,
                                 id_paziente BIGINT(20) UNSIGNED NOT NULL,
                                 PRIMARY KEY (id),
                                 UNIQUE KEY medici_pazienti_id_medico_id_paziente_unique (id_medico, id_paziente),
                                 KEY medici_pazienti_id_paziente_foreign (id_paziente)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE familiari_pazienti (
                                    id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                                    id_familiare BIGINT(20) UNSIGNED NOT NULL,
                                    id_paziente BIGINT(20) UNSIGNED NOT NULL,
                                    grado_parentela VARCHAR(30) DEFAULT NULL,
                                    PRIMARY KEY (id),
                                    UNIQUE KEY familiari_pazienti_id_familiare_id_paziente_unique (id_familiare, id_paziente),
                                    KEY familiari_pazienti_id_paziente_foreign (id_paziente)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================================
-- MQTT/IoT tables
-- =========================================================

CREATE TABLE telemetrie_dispositivo (
                                        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                                        id_dispositivo BIGINT(20) UNSIGNED NOT NULL,
                                        temperatura DECIMAL(5,2) DEFAULT NULL,
                                        umidita DECIMAL(5,2) DEFAULT NULL,
                                        allarme_attivo TINYINT(1) NOT NULL DEFAULT 0,
                                        wifi_rssi INT(11) DEFAULT NULL,
                                        scomparto_attuale INT(11) DEFAULT NULL,
                                        sveglia_impostata TIME DEFAULT NULL,
                                        timestamp_dispositivo DATETIME DEFAULT NULL,
                                        payload_json LONGTEXT DEFAULT NULL,
                                        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                                        PRIMARY KEY (id),
                                        KEY idx_tel_disp_data (id_dispositivo, timestamp_dispositivo),
                                        KEY idx_tel_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE eventi_dispositivo (
                                    id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                                    id_dispositivo BIGINT(20) UNSIGNED NOT NULL,
                                    id_paziente BIGINT(20) UNSIGNED DEFAULT NULL,
                                    id_assunzione BIGINT(20) UNSIGNED DEFAULT NULL,
                                    topic VARCHAR(150) DEFAULT NULL,
                                    azione VARCHAR(100) NOT NULL,
                                    metodo_attivazione VARCHAR(100) DEFAULT NULL,
                                    severita ENUM('info','warning','critico') NOT NULL DEFAULT 'info',
                                    messaggio TEXT DEFAULT NULL,
                                    timestamp_dispositivo DATETIME DEFAULT NULL,
                                    payload_json LONGTEXT DEFAULT NULL,
                                    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                                    PRIMARY KEY (id),
                                    KEY idx_eventi_disp_data (id_dispositivo, timestamp_dispositivo),
                                    KEY idx_eventi_azione (azione),
                                    KEY idx_eventi_assunzione (id_assunzione),
                                    KEY idx_eventi_paziente (id_paziente)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Optional backward-compatibility view for code that still reads from `utenti`
CREATE OR REPLACE VIEW utenti AS
SELECT id, nome, cognome, email, password, ruolo, telefono, created_at
FROM users;

-- =========================================================
-- Foreign keys
-- =========================================================

ALTER TABLE sessions
    ADD CONSTRAINT sessions_user_id_foreign
        FOREIGN KEY (user_id) REFERENCES users(id)
            ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE pazienti
    ADD CONSTRAINT pazienti_id_utente_foreign
        FOREIGN KEY (id_utente) REFERENCES users(id)
            ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE dispositivi
    ADD CONSTRAINT dispositivi_id_paziente_foreign
        FOREIGN KEY (id_paziente) REFERENCES pazienti(id)
            ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE terapie
    ADD CONSTRAINT terapie_id_paziente_foreign
        FOREIGN KEY (id_paziente) REFERENCES pazienti(id)
            ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT terapie_id_medico_foreign
  FOREIGN KEY (id_medico) REFERENCES users(id)
  ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT terapie_id_farmaco_foreign
  FOREIGN KEY (id_farmaco) REFERENCES farmaci(id)
  ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE somministrazioni
    ADD CONSTRAINT somministrazioni_id_terapia_foreign
        FOREIGN KEY (id_terapia) REFERENCES terapie(id)
            ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE assunzioni
    ADD CONSTRAINT assunzioni_id_somministrazione_foreign
        FOREIGN KEY (id_somministrazione) REFERENCES somministrazioni(id)
            ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT assunzioni_id_dispositivo_foreign
  FOREIGN KEY (id_dispositivo) REFERENCES dispositivi(id)
  ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE notifiche
    ADD CONSTRAINT notifiche_id_utente_foreign
        FOREIGN KEY (id_utente) REFERENCES users(id)
            ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT notifiche_id_paziente_foreign
  FOREIGN KEY (id_paziente) REFERENCES pazienti(id)
  ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT notifiche_id_dispositivo_foreign
  FOREIGN KEY (id_dispositivo) REFERENCES dispositivi(id)
  ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT notifiche_id_assunzione_foreign
  FOREIGN KEY (id_assunzione) REFERENCES assunzioni(id)
  ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE feedback
    ADD CONSTRAINT feedback_id_paziente_foreign
        FOREIGN KEY (id_paziente) REFERENCES pazienti(id)
            ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT feedback_id_assunzione_foreign
  FOREIGN KEY (id_assunzione) REFERENCES assunzioni(id)
  ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE medici_pazienti
    ADD CONSTRAINT medici_pazienti_id_medico_foreign
        FOREIGN KEY (id_medico) REFERENCES users(id)
            ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT medici_pazienti_id_paziente_foreign
  FOREIGN KEY (id_paziente) REFERENCES pazienti(id)
  ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE familiari_pazienti
    ADD CONSTRAINT familiari_pazienti_id_familiare_foreign
        FOREIGN KEY (id_familiare) REFERENCES users(id)
            ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT familiari_pazienti_id_paziente_foreign
  FOREIGN KEY (id_paziente) REFERENCES pazienti(id)
  ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE telemetrie_dispositivo
    ADD CONSTRAINT telemetrie_dispositivo_id_dispositivo_foreign
        FOREIGN KEY (id_dispositivo) REFERENCES dispositivi(id)
            ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE eventi_dispositivo
    ADD CONSTRAINT eventi_dispositivo_id_dispositivo_foreign
        FOREIGN KEY (id_dispositivo) REFERENCES dispositivi(id)
            ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT eventi_dispositivo_id_paziente_foreign
  FOREIGN KEY (id_paziente) REFERENCES pazienti(id)
  ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT eventi_dispositivo_id_assunzione_foreign
  FOREIGN KEY (id_assunzione) REFERENCES assunzioni(id)
  ON DELETE SET NULL ON UPDATE CASCADE;

SET FOREIGN_KEY_CHECKS = 1;
COMMIT;
