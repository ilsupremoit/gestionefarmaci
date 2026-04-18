DROP DATABASE IF EXISTS pillmate;
CREATE DATABASE pillmate CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE pillmate;

SET FOREIGN_KEY_CHECKS = 0;

CREATE TABLE utenti (
                        id INT(11) NOT NULL AUTO_INCREMENT,
                        nome VARCHAR(50) NOT NULL,
                        cognome VARCHAR(50) NOT NULL,
                        email VARCHAR(100) NOT NULL,
                        password VARCHAR(255) NOT NULL,
                        ruolo ENUM('paziente','medico','familiare','admin') NOT NULL,
                        telefono VARCHAR(20) DEFAULT NULL,
                        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                        PRIMARY KEY (id),
                        UNIQUE KEY email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE pazienti (
                          id INT(11) NOT NULL AUTO_INCREMENT,
                          id_utente INT(11) NOT NULL,
                          data_nascita DATE DEFAULT NULL,
                          indirizzo VARCHAR(150) DEFAULT NULL,
                          note_mediche TEXT DEFAULT NULL,
                          PRIMARY KEY (id),
                          UNIQUE KEY id_utente (id_utente)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE farmaci (
                         id INT(11) NOT NULL AUTO_INCREMENT,
                         nome VARCHAR(100) NOT NULL,
                         descrizione TEXT DEFAULT NULL,
                         dose VARCHAR(50) DEFAULT NULL,
                         note TEXT DEFAULT NULL,
                         PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE dispositivi (
                             id INT(11) NOT NULL AUTO_INCREMENT,
                             codice_seriale VARCHAR(100) NOT NULL,
                             id_paziente INT(11) NOT NULL,
                             nome_dispositivo VARCHAR(50) DEFAULT NULL,
                             stato ENUM('attivo','offline','manutenzione') DEFAULT 'attivo',
                             ultima_connessione DATETIME DEFAULT NULL,
                             batteria INT(11) DEFAULT NULL,
                             temperatura DECIMAL(5,2) DEFAULT NULL,
                             umidita DECIMAL(5,2) DEFAULT NULL,
                             wifi_rssi INT(11) DEFAULT NULL,
                             allarme_attivo TINYINT(1) DEFAULT 0,
                             scomparto_attuale INT(11) DEFAULT NULL,
                             sveglia_impostata TIME DEFAULT NULL,
                             ultimo_payload_at DATETIME DEFAULT NULL,
                             PRIMARY KEY (id),
                             UNIQUE KEY codice_seriale (codice_seriale),
                             KEY id_paziente (id_paziente)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE terapie (
                         id INT(11) NOT NULL AUTO_INCREMENT,
                         id_paziente INT(11) NOT NULL,
                         id_medico INT(11) NOT NULL,
                         id_farmaco INT(11) NOT NULL,
                         data_inizio DATE NOT NULL,
                         data_fine DATE DEFAULT NULL,
                         frequenza VARCHAR(50) DEFAULT NULL,
                         quantita INT(11) NOT NULL,
                         istruzioni TEXT DEFAULT NULL,
                         attiva TINYINT(1) DEFAULT 1,
                         PRIMARY KEY (id),
                         KEY id_paziente (id_paziente),
                         KEY id_medico (id_medico),
                         KEY id_farmaco (id_farmaco)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE somministrazioni (
                                  id INT(11) NOT NULL AUTO_INCREMENT,
                                  id_terapia INT(11) NOT NULL,
                                  ora TIME NOT NULL,
                                  giorno_settimana ENUM('Lun','Mar','Mer','Gio','Ven','Sab','Dom','Tutti') DEFAULT 'Tutti',
                                  PRIMARY KEY (id),
                                  KEY id_terapia (id_terapia)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE assunzioni (
                            id INT(11) NOT NULL AUTO_INCREMENT,
                            id_somministrazione INT(11) NOT NULL,
                            id_dispositivo INT(11) DEFAULT NULL,
                            data_prevista DATETIME NOT NULL,
                            data_erogazione DATETIME DEFAULT NULL,
                            data_conferma DATETIME DEFAULT NULL,
                            stato ENUM(
    'in_attesa',
    'erogata',
    'assunta',
    'saltata',
    'ritardo',
    'allarme_attivo',
    'apertura_forzata',
    'non_ritirata'
  ) DEFAULT 'in_attesa',
                            confermata_da ENUM('paziente','sensore','familiare','sistema') DEFAULT 'sistema',
                            allarme_inviato TINYINT(1) DEFAULT 0,
                            data_allarme DATETIME DEFAULT NULL,
                            apertura_forzata TINYINT(1) DEFAULT 0,
                            data_apertura_forzata DATETIME DEFAULT NULL,
                            note_evento TEXT DEFAULT NULL,
                            scomparto_numero INT(11) DEFAULT NULL,
                            PRIMARY KEY (id),
                            KEY id_somministrazione (id_somministrazione),
                            KEY idx_assunzioni_dispositivo (id_dispositivo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE notifiche (
                           id INT(11) NOT NULL AUTO_INCREMENT,
                           id_utente INT(11) NOT NULL,
                           id_paziente INT(11) DEFAULT NULL,
                           id_dispositivo INT(11) DEFAULT NULL,
                           id_assunzione INT(11) DEFAULT NULL,
                           titolo VARCHAR(100) NOT NULL,
                           messaggio TEXT NOT NULL,
                           tipo ENUM('promemoria','allarme','errore','info') DEFAULT 'info',
                           letta TINYINT(1) DEFAULT 0,
                           data_invio DATETIME DEFAULT CURRENT_TIMESTAMP,
                           PRIMARY KEY (id),
                           KEY id_utente (id_utente),
                           KEY idx_notifiche_paziente (id_paziente),
                           KEY idx_notifiche_dispositivo (id_dispositivo),
                           KEY idx_notifiche_assunzione (id_assunzione)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE feedback (
                          id INT(11) NOT NULL AUTO_INCREMENT,
                          id_paziente INT(11) NOT NULL,
                          id_assunzione INT(11) DEFAULT NULL,
                          messaggio TEXT NOT NULL,
                          stato_salute VARCHAR(100) DEFAULT NULL,
                          data_feedback DATETIME DEFAULT CURRENT_TIMESTAMP,
                          PRIMARY KEY (id),
                          KEY id_paziente (id_paziente),
                          KEY id_assunzione (id_assunzione)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE medici_pazienti (
                                 id INT(11) NOT NULL AUTO_INCREMENT,
                                 id_medico INT(11) NOT NULL,
                                 id_paziente INT(11) NOT NULL,
                                 PRIMARY KEY (id),
                                 UNIQUE KEY id_medico (id_medico,id_paziente),
                                 KEY id_paziente (id_paziente)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE familiari_pazienti (
                                    id INT(11) NOT NULL AUTO_INCREMENT,
                                    id_familiare INT(11) NOT NULL,
                                    id_paziente INT(11) NOT NULL,
                                    grado_parentela VARCHAR(30) DEFAULT NULL,
                                    PRIMARY KEY (id),
                                    UNIQUE KEY id_familiare (id_familiare,id_paziente),
                                    KEY id_paziente (id_paziente)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE telemetrie_dispositivo (
                                        id INT(11) NOT NULL AUTO_INCREMENT,
                                        id_dispositivo INT(11) NOT NULL,
                                        temperatura DECIMAL(5,2) DEFAULT NULL,
                                        umidita DECIMAL(5,2) DEFAULT NULL,
                                        allarme_attivo TINYINT(1) DEFAULT 0,
                                        wifi_rssi INT(11) DEFAULT NULL,
                                        scomparto_attuale INT(11) DEFAULT NULL,
                                        sveglia_impostata TIME DEFAULT NULL,
                                        timestamp_dispositivo DATETIME DEFAULT NULL,
                                        payload_json LONGTEXT DEFAULT NULL,
                                        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                                        PRIMARY KEY (id),
                                        KEY idx_tel_disp_data (id_dispositivo, timestamp_dispositivo),
                                        KEY idx_tel_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE eventi_dispositivo (
                                    id INT(11) NOT NULL AUTO_INCREMENT,
                                    id_dispositivo INT(11) NOT NULL,
                                    id_paziente INT(11) DEFAULT NULL,
                                    id_assunzione INT(11) DEFAULT NULL,
                                    topic VARCHAR(150) DEFAULT NULL,
                                    azione VARCHAR(100) NOT NULL,
                                    metodo_attivazione VARCHAR(100) DEFAULT NULL,
                                    severita ENUM('info','warning','critico') DEFAULT 'info',
                                    messaggio TEXT DEFAULT NULL,
                                    timestamp_dispositivo DATETIME DEFAULT NULL,
                                    payload_json LONGTEXT DEFAULT NULL,
                                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                                    PRIMARY KEY (id),
                                    KEY idx_eventi_disp_data (id_dispositivo, timestamp_dispositivo),
                                    KEY idx_eventi_azione (azione),
                                    KEY idx_eventi_assunzione (id_assunzione),
                                    KEY idx_eventi_paziente (id_paziente)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

ALTER TABLE pazienti
    ADD CONSTRAINT pazienti_ibfk_1
        FOREIGN KEY (id_utente) REFERENCES utenti(id)
            ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE dispositivi
    ADD CONSTRAINT dispositivi_ibfk_1
        FOREIGN KEY (id_paziente) REFERENCES pazienti(id)
            ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE terapie
    ADD CONSTRAINT terapie_ibfk_1
        FOREIGN KEY (id_paziente) REFERENCES pazienti(id)
            ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT terapie_ibfk_2
  FOREIGN KEY (id_medico) REFERENCES utenti(id)
  ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT terapie_ibfk_3
  FOREIGN KEY (id_farmaco) REFERENCES farmaci(id)
  ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE somministrazioni
    ADD CONSTRAINT somministrazioni_ibfk_1
        FOREIGN KEY (id_terapia) REFERENCES terapie(id)
            ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE assunzioni
    ADD CONSTRAINT assunzioni_ibfk_1
        FOREIGN KEY (id_somministrazione) REFERENCES somministrazioni(id)
            ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT fk_assunzioni_dispositivo
  FOREIGN KEY (id_dispositivo) REFERENCES dispositivi(id)
  ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE notifiche
    ADD CONSTRAINT notifiche_ibfk_1
        FOREIGN KEY (id_utente) REFERENCES utenti(id)
            ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT fk_notifiche_paziente
  FOREIGN KEY (id_paziente) REFERENCES pazienti(id)
  ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT fk_notifiche_dispositivo
  FOREIGN KEY (id_dispositivo) REFERENCES dispositivi(id)
  ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT fk_notifiche_assunzione
  FOREIGN KEY (id_assunzione) REFERENCES assunzioni(id)
  ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE feedback
    ADD CONSTRAINT feedback_ibfk_1
        FOREIGN KEY (id_paziente) REFERENCES pazienti(id)
            ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT feedback_ibfk_2
  FOREIGN KEY (id_assunzione) REFERENCES assunzioni(id)
  ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE medici_pazienti
    ADD CONSTRAINT medici_pazienti_ibfk_1
        FOREIGN KEY (id_medico) REFERENCES utenti(id)
            ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT medici_pazienti_ibfk_2
  FOREIGN KEY (id_paziente) REFERENCES pazienti(id)
  ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE familiari_pazienti
    ADD CONSTRAINT familiari_pazienti_ibfk_1
        FOREIGN KEY (id_familiare) REFERENCES utenti(id)
            ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT familiari_pazienti_ibfk_2
  FOREIGN KEY (id_paziente) REFERENCES pazienti(id)
  ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE telemetrie_dispositivo
    ADD CONSTRAINT fk_telemetrie_dispositivo
        FOREIGN KEY (id_dispositivo) REFERENCES dispositivi(id)
            ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE eventi_dispositivo
    ADD CONSTRAINT fk_eventi_dispositivo
        FOREIGN KEY (id_dispositivo) REFERENCES dispositivi(id)
            ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT fk_eventi_paziente
  FOREIGN KEY (id_paziente) REFERENCES pazienti(id)
  ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT fk_eventi_assunzione
  FOREIGN KEY (id_assunzione) REFERENCES assunzioni(id)
  ON DELETE SET NULL ON UPDATE CASCADE;

SET FOREIGN_KEY_CHECKS = 1;
