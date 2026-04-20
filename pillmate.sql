-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Creato il: Apr 20, 2026 alle 12:09
-- Versione del server: 10.4.32-MariaDB
-- Versione PHP: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `pillmate`
--

-- --------------------------------------------------------

--
-- Struttura della tabella `assunzioni`
--

CREATE TABLE `assunzioni` (
                              `id` bigint(20) UNSIGNED NOT NULL,
                              `id_somministrazione` bigint(20) UNSIGNED NOT NULL,
                              `id_dispositivo` bigint(20) UNSIGNED DEFAULT NULL,
                              `data_prevista` datetime NOT NULL,
                              `data_erogazione` datetime DEFAULT NULL,
                              `data_conferma` datetime DEFAULT NULL,
                              `stato` enum('in_attesa','erogata','assunta','saltata','ritardo','allarme_attivo','apertura_forzata','non_ritirata') NOT NULL DEFAULT 'in_attesa',
                              `confermata_da` enum('paziente','sensore','familiare','sistema') NOT NULL DEFAULT 'sistema',
                              `allarme_inviato` tinyint(1) NOT NULL DEFAULT 0,
                              `data_allarme` datetime DEFAULT NULL,
                              `apertura_forzata` tinyint(1) NOT NULL DEFAULT 0,
                              `data_apertura_forzata` datetime DEFAULT NULL,
                              `note_evento` text DEFAULT NULL,
                              `scomparto_numero` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `cache`
--

CREATE TABLE `cache` (
                         `key` varchar(255) NOT NULL,
                         `value` mediumtext NOT NULL,
                         `expiration` bigint(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `cache_locks`
--

CREATE TABLE `cache_locks` (
                               `key` varchar(255) NOT NULL,
                               `owner` varchar(255) NOT NULL,
                               `expiration` bigint(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `dispositivi`
--

CREATE TABLE `dispositivi` (
                               `id` bigint(20) UNSIGNED NOT NULL,
                               `codice_seriale` varchar(100) NOT NULL,
                               `id_paziente` bigint(20) UNSIGNED NOT NULL,
                               `nome_dispositivo` varchar(50) DEFAULT NULL,
                               `stato` enum('attivo','offline','manutenzione') NOT NULL DEFAULT 'attivo',
                               `ultima_connessione` datetime DEFAULT NULL,
                               `batteria` int(11) DEFAULT NULL,
                               `temperatura` decimal(5,2) DEFAULT NULL,
                               `umidita` decimal(5,2) DEFAULT NULL,
                               `wifi_rssi` int(11) DEFAULT NULL,
                               `allarme_attivo` tinyint(1) NOT NULL DEFAULT 0,
                               `scomparto_attuale` int(11) DEFAULT NULL,
                               `sveglia_impostata` time DEFAULT NULL,
                               `ultimo_payload_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dump dei dati per la tabella `dispositivi`
--

INSERT INTO `dispositivi` (`id`, `codice_seriale`, `id_paziente`, `nome_dispositivo`, `stato`, `ultima_connessione`, `batteria`, `temperatura`, `umidita`, `wifi_rssi`, `allarme_attivo`, `scomparto_attuale`, `sveglia_impostata`, `ultimo_payload_at`) VALUES
    (1, 'coglione 1', 2, 'COGLIO  = SCEMO SCEMO', 'offline', NULL, NULL, NULL, NULL, NULL, 0, NULL, '11:01:00', NULL);

-- --------------------------------------------------------

--
-- Struttura della tabella `eventi_dispositivo`
--

CREATE TABLE `eventi_dispositivo` (
                                      `id` bigint(20) UNSIGNED NOT NULL,
                                      `id_dispositivo` bigint(20) UNSIGNED NOT NULL,
                                      `id_paziente` bigint(20) UNSIGNED DEFAULT NULL,
                                      `id_assunzione` bigint(20) UNSIGNED DEFAULT NULL,
                                      `topic` varchar(150) DEFAULT NULL,
                                      `azione` varchar(100) NOT NULL,
                                      `metodo_attivazione` varchar(100) DEFAULT NULL,
                                      `severita` enum('info','warning','critico') NOT NULL DEFAULT 'info',
                                      `messaggio` text DEFAULT NULL,
                                      `timestamp_dispositivo` datetime DEFAULT NULL,
                                      `payload_json` longtext DEFAULT NULL,
                                      `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dump dei dati per la tabella `eventi_dispositivo`
--

INSERT INTO `eventi_dispositivo` (`id`, `id_dispositivo`, `id_paziente`, `id_assunzione`, `topic`, `azione`, `metodo_attivazione`, `severita`, `messaggio`, `timestamp_dispositivo`, `payload_json`, `created_at`) VALUES
                                                                                                                                                                                                                       (1, 1, 2, NULL, 'pillmate/coglione 1/comandi', 'imposta_sveglia', 'medico_web', 'info', 'Comando \'imposta_sveglia\' inviato dal Dr. moustakim', NULL, '{\"azione\":\"imposta_sveglia\",\"medico_id\":1,\"timestamp\":\"2026-04-20 08:57:01\",\"ora\":\"11:01\"}', '2026-04-20 08:57:04'),
                                                                                                                                                                                                                       (2, 1, 2, NULL, 'pillmate/coglione 1/comandi', 'reset', 'medico_web', 'info', 'Comando \'reset\' inviato dal Dr. moustakim', NULL, '{\"azione\":\"reset\",\"medico_id\":1,\"timestamp\":\"2026-04-20 08:57:05\"}', '2026-04-20 08:57:05');

-- --------------------------------------------------------

--
-- Struttura della tabella `failed_jobs`
--

CREATE TABLE `failed_jobs` (
                               `id` bigint(20) UNSIGNED NOT NULL,
                               `uuid` varchar(255) NOT NULL,
                               `connection` text NOT NULL,
                               `queue` text NOT NULL,
                               `payload` longtext NOT NULL,
                               `exception` longtext NOT NULL,
                               `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `familiari_pazienti`
--

CREATE TABLE `familiari_pazienti` (
                                      `id` bigint(20) UNSIGNED NOT NULL,
                                      `id_familiare` bigint(20) UNSIGNED NOT NULL,
                                      `id_paziente` bigint(20) UNSIGNED NOT NULL,
                                      `grado_parentela` varchar(30) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `farmaci`
--

CREATE TABLE `farmaci` (
                           `id` bigint(20) UNSIGNED NOT NULL,
                           `nome` varchar(100) NOT NULL,
                           `descrizione` text DEFAULT NULL,
                           `dose` varchar(50) DEFAULT NULL,
                           `note` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `feedback`
--

CREATE TABLE `feedback` (
                            `id` bigint(20) UNSIGNED NOT NULL,
                            `id_paziente` bigint(20) UNSIGNED NOT NULL,
                            `id_assunzione` bigint(20) UNSIGNED DEFAULT NULL,
                            `messaggio` text NOT NULL,
                            `stato_salute` varchar(100) DEFAULT NULL,
                            `data_feedback` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `jobs`
--

CREATE TABLE `jobs` (
                        `id` bigint(20) UNSIGNED NOT NULL,
                        `queue` varchar(255) NOT NULL,
                        `payload` longtext NOT NULL,
                        `attempts` tinyint(3) UNSIGNED NOT NULL,
                        `reserved_at` int(10) UNSIGNED DEFAULT NULL,
                        `available_at` int(10) UNSIGNED NOT NULL,
                        `created_at` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `job_batches`
--

CREATE TABLE `job_batches` (
                               `id` varchar(255) NOT NULL,
                               `name` varchar(255) NOT NULL,
                               `total_jobs` int(11) NOT NULL,
                               `pending_jobs` int(11) NOT NULL,
                               `failed_jobs` int(11) NOT NULL,
                               `failed_job_ids` longtext NOT NULL,
                               `options` mediumtext DEFAULT NULL,
                               `cancelled_at` int(11) DEFAULT NULL,
                               `created_at` int(11) NOT NULL,
                               `finished_at` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `medici_pazienti`
--

CREATE TABLE `medici_pazienti` (
                                   `id` bigint(20) UNSIGNED NOT NULL,
                                   `id_medico` bigint(20) UNSIGNED NOT NULL,
                                   `id_paziente` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dump dei dati per la tabella `medici_pazienti`
--

INSERT INTO `medici_pazienti` (`id`, `id_medico`, `id_paziente`) VALUES
                                                                     (1, 1, 1),
                                                                     (2, 1, 2);

-- --------------------------------------------------------

--
-- Struttura della tabella `migrations`
--

CREATE TABLE `migrations` (
                              `id` int(10) UNSIGNED NOT NULL,
                              `migration` varchar(255) NOT NULL,
                              `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dump dei dati per la tabella `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
                                                          (1, '0001_01_01_000000_create_users_table', 1),
                                                          (2, '0001_01_01_000001_create_cache_table', 1),
                                                          (3, '0001_01_01_000002_create_jobs_table', 1),
                                                          (4, '2026_04_17_094201_alter_users_for_invited_patients', 1),
                                                          (5, '2026_04_18_000001_add_iot_fields_to_dispositivi_and_assunzioni', 1),
                                                          (6, '2026_04_18_000002_create_telemetrie_and_eventi_dispositivo', 1);

-- --------------------------------------------------------

--
-- Struttura della tabella `notifiche`
--

CREATE TABLE `notifiche` (
                             `id` bigint(20) UNSIGNED NOT NULL,
                             `id_utente` bigint(20) UNSIGNED NOT NULL,
                             `id_mittente` bigint(20) UNSIGNED DEFAULT NULL,
                             `id_paziente` bigint(20) UNSIGNED DEFAULT NULL,
                             `id_dispositivo` bigint(20) UNSIGNED DEFAULT NULL,
                             `id_assunzione` bigint(20) UNSIGNED DEFAULT NULL,
                             `titolo` varchar(100) NOT NULL,
                             `messaggio` text NOT NULL,
                             `tipo` enum('promemoria','allarme','errore','info','messaggio') NOT NULL DEFAULT 'info',
                             `letta` tinyint(1) NOT NULL DEFAULT 0,
                             `data_invio` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
                                         `email` varchar(255) NOT NULL,
                                         `token` varchar(255) NOT NULL,
                                         `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `pazienti`
--

CREATE TABLE `pazienti` (
                            `id` bigint(20) UNSIGNED NOT NULL,
                            `id_utente` bigint(20) UNSIGNED NOT NULL,
                            `data_nascita` date DEFAULT NULL,
                            `indirizzo` varchar(150) DEFAULT NULL,
                            `note_mediche` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dump dei dati per la tabella `pazienti`
--

INSERT INTO `pazienti` (`id`, `id_utente`, `data_nascita`, `indirizzo`, `note_mediche`) VALUES
                                                                                            (1, 3, '2003-02-03', 'via degli alpini 26', 'per i pidoccbhi'),
                                                                                            (2, 4, '2002-02-03', 'via degli alpini 26', 'CANCRO');

-- --------------------------------------------------------

--
-- Struttura della tabella `sessions`
--

CREATE TABLE `sessions` (
                            `id` varchar(255) NOT NULL,
                            `user_id` bigint(20) UNSIGNED DEFAULT NULL,
                            `ip_address` varchar(45) DEFAULT NULL,
                            `user_agent` text DEFAULT NULL,
                            `payload` longtext NOT NULL,
                            `last_activity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `somministrazioni`
--

CREATE TABLE `somministrazioni` (
                                    `id` bigint(20) UNSIGNED NOT NULL,
                                    `id_terapia` bigint(20) UNSIGNED NOT NULL,
                                    `ora` time NOT NULL,
                                    `giorno_settimana` enum('Lun','Mar','Mer','Gio','Ven','Sab','Dom','Tutti') NOT NULL DEFAULT 'Tutti'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `telemetrie_dispositivo`
--

CREATE TABLE `telemetrie_dispositivo` (
                                          `id` bigint(20) UNSIGNED NOT NULL,
                                          `id_dispositivo` bigint(20) UNSIGNED NOT NULL,
                                          `temperatura` decimal(5,2) DEFAULT NULL,
                                          `umidita` decimal(5,2) DEFAULT NULL,
                                          `allarme_attivo` tinyint(1) NOT NULL DEFAULT 0,
                                          `wifi_rssi` int(11) DEFAULT NULL,
                                          `scomparto_attuale` int(11) DEFAULT NULL,
                                          `sveglia_impostata` time DEFAULT NULL,
                                          `timestamp_dispositivo` datetime DEFAULT NULL,
                                          `payload_json` longtext DEFAULT NULL,
                                          `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `terapie`
--

CREATE TABLE `terapie` (
                           `id` bigint(20) UNSIGNED NOT NULL,
                           `id_paziente` bigint(20) UNSIGNED NOT NULL,
                           `id_medico` bigint(20) UNSIGNED NOT NULL,
                           `id_farmaco` bigint(20) UNSIGNED NOT NULL,
                           `data_inizio` date NOT NULL,
                           `data_fine` date DEFAULT NULL,
                           `frequenza` varchar(50) DEFAULT NULL,
                           `quantita` int(11) NOT NULL,
                           `istruzioni` text DEFAULT NULL,
                           `attiva` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `users`
--

CREATE TABLE `users` (
                         `id` bigint(20) UNSIGNED NOT NULL,
                         `nome` varchar(50) NOT NULL,
                         `cognome` varchar(50) NOT NULL,
                         `username` varchar(50) DEFAULT NULL,
                         `email` varchar(100) DEFAULT NULL,
                         `email_verified_at` timestamp NULL DEFAULT NULL,
                         `password` varchar(255) NOT NULL,
                         `must_change_password` tinyint(1) NOT NULL DEFAULT 1,
                         `ruolo` enum('paziente','medico','familiare','admin') NOT NULL,
                         `telefono` varchar(20) DEFAULT NULL,
                         `remember_token` varchar(100) DEFAULT NULL,
                         `created_at` timestamp NULL DEFAULT NULL,
                         `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dump dei dati per la tabella `users`
--

INSERT INTO `users` (`id`, `nome`, `cognome`, `username`, `email`, `email_verified_at`, `password`, `must_change_password`, `ruolo`, `telefono`, `remember_token`, `created_at`, `updated_at`) VALUES
                                                                                                                                                                                                   (1, 'adam', 'moustakim', 'adam', 'moustakimadam@gmail.com', NULL, '$2y$12$PeUlIBe81vHrUZMQ05HjiOSJWOfC57.E/Zd82puZc.2LvFj6l4dBi', 0, 'medico', NULL, NULL, NULL, '2026-04-18 08:33:57'),
                                                                                                                                                                                                   (3, 'kemmach', 'moud', 'fucknigga', 'nigga@gmail.com', NULL, '$2y$12$N2zCma/4reNXk9sudgP5lesjBii3150VZQ1xqhKdIXBVUeYUFoPey', 1, 'paziente', '3512834852', NULL, '2026-04-18 08:49:26', '2026-04-18 08:49:26'),
                                                                                                                                                                                                   (4, 'BLACK', 'NIGGERS', 'BLACKNIGGA', 'CANCRO@NIGGA.IT', NULL, '$2y$12$zKfP.bDCyNOZHoFFAeZ3rOq0dxcTEJqggwoe1YndOATGoEzyGkSFO', 0, 'paziente', '3512834852', NULL, '2026-04-20 05:50:08', '2026-04-20 06:13:32');

-- --------------------------------------------------------

--
-- Struttura stand-in per le viste `utenti`
-- (Vedi sotto per la vista effettiva)
--
CREATE TABLE `utenti` (
                          `id` bigint(20) unsigned
    ,`nome` varchar(50)
    ,`cognome` varchar(50)
    ,`email` varchar(100)
    ,`password` varchar(255)
    ,`ruolo` enum('paziente','medico','familiare','admin')
    ,`telefono` varchar(20)
    ,`created_at` timestamp
);

-- --------------------------------------------------------

--
-- Struttura per vista `utenti`
--
DROP TABLE IF EXISTS `utenti`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `utenti`  AS SELECT `users`.`id` AS `id`, `users`.`nome` AS `nome`, `users`.`cognome` AS `cognome`, `users`.`email` AS `email`, `users`.`password` AS `password`, `users`.`ruolo` AS `ruolo`, `users`.`telefono` AS `telefono`, `users`.`created_at` AS `created_at` FROM `users` ;

--
-- Indici per le tabelle scaricate
--

--
-- Indici per le tabelle `assunzioni`
--
ALTER TABLE `assunzioni`
    ADD PRIMARY KEY (`id`),
  ADD KEY `assunzioni_id_somministrazione_foreign` (`id_somministrazione`),
  ADD KEY `assunzioni_id_dispositivo_foreign` (`id_dispositivo`);

--
-- Indici per le tabelle `cache`
--
ALTER TABLE `cache`
    ADD PRIMARY KEY (`key`),
  ADD KEY `cache_expiration_index` (`expiration`);

--
-- Indici per le tabelle `cache_locks`
--
ALTER TABLE `cache_locks`
    ADD PRIMARY KEY (`key`),
  ADD KEY `cache_locks_expiration_index` (`expiration`);

--
-- Indici per le tabelle `dispositivi`
--
ALTER TABLE `dispositivi`
    ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `dispositivi_codice_seriale_unique` (`codice_seriale`),
  ADD KEY `dispositivi_id_paziente_foreign` (`id_paziente`);

--
-- Indici per le tabelle `eventi_dispositivo`
--
ALTER TABLE `eventi_dispositivo`
    ADD PRIMARY KEY (`id`),
  ADD KEY `idx_eventi_disp_data` (`id_dispositivo`,`timestamp_dispositivo`),
  ADD KEY `idx_eventi_azione` (`azione`),
  ADD KEY `idx_eventi_assunzione` (`id_assunzione`),
  ADD KEY `idx_eventi_paziente` (`id_paziente`);

--
-- Indici per le tabelle `failed_jobs`
--
ALTER TABLE `failed_jobs`
    ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indici per le tabelle `familiari_pazienti`
--
ALTER TABLE `familiari_pazienti`
    ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `familiari_pazienti_id_familiare_id_paziente_unique` (`id_familiare`,`id_paziente`),
  ADD KEY `familiari_pazienti_id_paziente_foreign` (`id_paziente`);

--
-- Indici per le tabelle `farmaci`
--
ALTER TABLE `farmaci`
    ADD PRIMARY KEY (`id`);

--
-- Indici per le tabelle `feedback`
--
ALTER TABLE `feedback`
    ADD PRIMARY KEY (`id`),
  ADD KEY `feedback_id_paziente_foreign` (`id_paziente`),
  ADD KEY `feedback_id_assunzione_foreign` (`id_assunzione`);

--
-- Indici per le tabelle `jobs`
--
ALTER TABLE `jobs`
    ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indici per le tabelle `job_batches`
--
ALTER TABLE `job_batches`
    ADD PRIMARY KEY (`id`);

--
-- Indici per le tabelle `medici_pazienti`
--
ALTER TABLE `medici_pazienti`
    ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `medici_pazienti_id_medico_id_paziente_unique` (`id_medico`,`id_paziente`),
  ADD KEY `medici_pazienti_id_paziente_foreign` (`id_paziente`);

--
-- Indici per le tabelle `migrations`
--
ALTER TABLE `migrations`
    ADD PRIMARY KEY (`id`);

--
-- Indici per le tabelle `notifiche`
--
ALTER TABLE `notifiche`
    ADD PRIMARY KEY (`id`),
  ADD KEY `notifiche_id_utente_foreign` (`id_utente`),
  ADD KEY `notifiche_id_mittente_foreign` (`id_mittente`),
  ADD KEY `notifiche_id_paziente_foreign` (`id_paziente`),
  ADD KEY `notifiche_id_dispositivo_foreign` (`id_dispositivo`),
  ADD KEY `notifiche_id_assunzione_foreign` (`id_assunzione`);

--
-- Indici per le tabelle `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
    ADD PRIMARY KEY (`email`);

--
-- Indici per le tabelle `pazienti`
--
ALTER TABLE `pazienti`
    ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `pazienti_id_utente_unique` (`id_utente`);

--
-- Indici per le tabelle `sessions`
--
ALTER TABLE `sessions`
    ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indici per le tabelle `somministrazioni`
--
ALTER TABLE `somministrazioni`
    ADD PRIMARY KEY (`id`),
  ADD KEY `somministrazioni_id_terapia_foreign` (`id_terapia`);

--
-- Indici per le tabelle `telemetrie_dispositivo`
--
ALTER TABLE `telemetrie_dispositivo`
    ADD PRIMARY KEY (`id`),
  ADD KEY `idx_tel_disp_data` (`id_dispositivo`,`timestamp_dispositivo`),
  ADD KEY `idx_tel_created` (`created_at`);

--
-- Indici per le tabelle `terapie`
--
ALTER TABLE `terapie`
    ADD PRIMARY KEY (`id`),
  ADD KEY `terapie_id_paziente_foreign` (`id_paziente`),
  ADD KEY `terapie_id_medico_foreign` (`id_medico`),
  ADD KEY `terapie_id_farmaco_foreign` (`id_farmaco`);

--
-- Indici per le tabelle `users`
--
ALTER TABLE `users`
    ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`),
  ADD UNIQUE KEY `users_username_unique` (`username`);

--
-- AUTO_INCREMENT per le tabelle scaricate
--

--
-- AUTO_INCREMENT per la tabella `assunzioni`
--
ALTER TABLE `assunzioni`
    MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `dispositivi`
--
ALTER TABLE `dispositivi`
    MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT per la tabella `eventi_dispositivo`
--
ALTER TABLE `eventi_dispositivo`
    MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT per la tabella `failed_jobs`
--
ALTER TABLE `failed_jobs`
    MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `familiari_pazienti`
--
ALTER TABLE `familiari_pazienti`
    MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `farmaci`
--
ALTER TABLE `farmaci`
    MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `feedback`
--
ALTER TABLE `feedback`
    MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `jobs`
--
ALTER TABLE `jobs`
    MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `medici_pazienti`
--
ALTER TABLE `medici_pazienti`
    MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT per la tabella `migrations`
--
ALTER TABLE `migrations`
    MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT per la tabella `notifiche`
--
ALTER TABLE `notifiche`
    MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `pazienti`
--
ALTER TABLE `pazienti`
    MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT per la tabella `somministrazioni`
--
ALTER TABLE `somministrazioni`
    MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `telemetrie_dispositivo`
--
ALTER TABLE `telemetrie_dispositivo`
    MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `terapie`
--
ALTER TABLE `terapie`
    MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `users`
--
ALTER TABLE `users`
    MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Limiti per le tabelle scaricate
--

--
-- Limiti per la tabella `assunzioni`
--
ALTER TABLE `assunzioni`
    ADD CONSTRAINT `assunzioni_id_dispositivo_foreign` FOREIGN KEY (`id_dispositivo`) REFERENCES `dispositivi` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `assunzioni_id_somministrazione_foreign` FOREIGN KEY (`id_somministrazione`) REFERENCES `somministrazioni` (`id`) ON DELETE CASCADE;

--
-- Limiti per la tabella `dispositivi`
--
ALTER TABLE `dispositivi`
    ADD CONSTRAINT `dispositivi_id_paziente_foreign` FOREIGN KEY (`id_paziente`) REFERENCES `pazienti` (`id`) ON DELETE CASCADE;

--
-- Limiti per la tabella `eventi_dispositivo`
--
ALTER TABLE `eventi_dispositivo`
    ADD CONSTRAINT `eventi_dispositivo_id_assunzione_foreign` FOREIGN KEY (`id_assunzione`) REFERENCES `assunzioni` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `eventi_dispositivo_id_dispositivo_foreign` FOREIGN KEY (`id_dispositivo`) REFERENCES `dispositivi` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `eventi_dispositivo_id_paziente_foreign` FOREIGN KEY (`id_paziente`) REFERENCES `pazienti` (`id`) ON DELETE SET NULL;

--
-- Limiti per la tabella `familiari_pazienti`
--
ALTER TABLE `familiari_pazienti`
    ADD CONSTRAINT `familiari_pazienti_id_familiare_foreign` FOREIGN KEY (`id_familiare`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `familiari_pazienti_id_paziente_foreign` FOREIGN KEY (`id_paziente`) REFERENCES `pazienti` (`id`) ON DELETE CASCADE;

--
-- Limiti per la tabella `feedback`
--
ALTER TABLE `feedback`
    ADD CONSTRAINT `feedback_id_assunzione_foreign` FOREIGN KEY (`id_assunzione`) REFERENCES `assunzioni` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `feedback_id_paziente_foreign` FOREIGN KEY (`id_paziente`) REFERENCES `pazienti` (`id`) ON DELETE CASCADE;

--
-- Limiti per la tabella `medici_pazienti`
--
ALTER TABLE `medici_pazienti`
    ADD CONSTRAINT `medici_pazienti_id_medico_foreign` FOREIGN KEY (`id_medico`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `medici_pazienti_id_paziente_foreign` FOREIGN KEY (`id_paziente`) REFERENCES `pazienti` (`id`) ON DELETE CASCADE;

--
-- Limiti per la tabella `notifiche`
--
ALTER TABLE `notifiche`
    ADD CONSTRAINT `notifiche_id_assunzione_foreign` FOREIGN KEY (`id_assunzione`) REFERENCES `assunzioni` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `notifiche_id_dispositivo_foreign` FOREIGN KEY (`id_dispositivo`) REFERENCES `dispositivi` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `notifiche_id_mittente_foreign` FOREIGN KEY (`id_mittente`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `notifiche_id_paziente_foreign` FOREIGN KEY (`id_paziente`) REFERENCES `pazienti` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `notifiche_id_utente_foreign` FOREIGN KEY (`id_utente`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Limiti per la tabella `pazienti`
--
ALTER TABLE `pazienti`
    ADD CONSTRAINT `pazienti_id_utente_foreign` FOREIGN KEY (`id_utente`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Limiti per la tabella `somministrazioni`
--
ALTER TABLE `somministrazioni`
    ADD CONSTRAINT `somministrazioni_id_terapia_foreign` FOREIGN KEY (`id_terapia`) REFERENCES `terapie` (`id`) ON DELETE CASCADE;

--
-- Limiti per la tabella `telemetrie_dispositivo`
--
ALTER TABLE `telemetrie_dispositivo`
    ADD CONSTRAINT `telemetrie_dispositivo_id_dispositivo_foreign` FOREIGN KEY (`id_dispositivo`) REFERENCES `dispositivi` (`id`) ON DELETE CASCADE;

--
-- Limiti per la tabella `terapie`
--
ALTER TABLE `terapie`
    ADD CONSTRAINT `terapie_id_farmaco_foreign` FOREIGN KEY (`id_farmaco`) REFERENCES `farmaci` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `terapie_id_medico_foreign` FOREIGN KEY (`id_medico`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `terapie_id_paziente_foreign` FOREIGN KEY (`id_paziente`) REFERENCES `pazienti` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
