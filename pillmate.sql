-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Creato il: Mar 28, 2026 alle 08:56
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
  `id` int(11) NOT NULL,
  `id_somministrazione` int(11) NOT NULL,
  `data_prevista` datetime NOT NULL,
  `data_erogazione` datetime DEFAULT NULL,
  `data_conferma` datetime DEFAULT NULL,
  `stato` enum('in_attesa','erogata','assunta','saltata','ritardo') DEFAULT 'in_attesa',
  `confermata_da` enum('paziente','sensore','familiare','sistema') DEFAULT 'sistema'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `dispositivi`
--

CREATE TABLE `dispositivi` (
  `id` int(11) NOT NULL,
  `codice_seriale` varchar(100) NOT NULL,
  `id_paziente` int(11) NOT NULL,
  `nome_dispositivo` varchar(50) DEFAULT NULL,
  `stato` enum('attivo','offline','manutenzione') DEFAULT 'attivo',
  `ultima_connessione` datetime DEFAULT NULL,
  `batteria` int(11) DEFAULT NULL,
  `temperatura` decimal(5,2) DEFAULT NULL,
  `umidita` decimal(5,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `familiari_pazienti`
--

CREATE TABLE `familiari_pazienti` (
  `id` int(11) NOT NULL,
  `id_familiare` int(11) NOT NULL,
  `id_paziente` int(11) NOT NULL,
  `grado_parentela` varchar(30) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `farmaci`
--

CREATE TABLE `farmaci` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `descrizione` text DEFAULT NULL,
  `dose` varchar(50) DEFAULT NULL,
  `note` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `feedback`
--

CREATE TABLE `feedback` (
  `id` int(11) NOT NULL,
  `id_paziente` int(11) NOT NULL,
  `id_assunzione` int(11) DEFAULT NULL,
  `messaggio` text NOT NULL,
  `stato_salute` varchar(100) DEFAULT NULL,
  `data_feedback` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `medici_pazienti`
--

CREATE TABLE `medici_pazienti` (
  `id` int(11) NOT NULL,
  `id_medico` int(11) NOT NULL,
  `id_paziente` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `notifiche`
--

CREATE TABLE `notifiche` (
  `id` int(11) NOT NULL,
  `id_utente` int(11) NOT NULL,
  `titolo` varchar(100) NOT NULL,
  `messaggio` text NOT NULL,
  `tipo` enum('promemoria','allarme','errore','info') DEFAULT 'info',
  `letta` tinyint(1) DEFAULT 0,
  `data_invio` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `pazienti`
--

CREATE TABLE `pazienti` (
  `id` int(11) NOT NULL,
  `id_utente` int(11) NOT NULL,
  `data_nascita` date DEFAULT NULL,
  `indirizzo` varchar(150) DEFAULT NULL,
  `note_mediche` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `somministrazioni`
--

CREATE TABLE `somministrazioni` (
  `id` int(11) NOT NULL,
  `id_terapia` int(11) NOT NULL,
  `ora` time NOT NULL,
  `giorno_settimana` enum('Lun','Mar','Mer','Gio','Ven','Sab','Dom','Tutti') DEFAULT 'Tutti'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `terapie`
--

CREATE TABLE `terapie` (
  `id` int(11) NOT NULL,
  `id_paziente` int(11) NOT NULL,
  `id_medico` int(11) NOT NULL,
  `id_farmaco` int(11) NOT NULL,
  `data_inizio` date NOT NULL,
  `data_fine` date DEFAULT NULL,
  `frequenza` varchar(50) DEFAULT NULL,
  `quantita` int(11) NOT NULL,
  `istruzioni` text DEFAULT NULL,
  `attiva` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `utenti`
--

CREATE TABLE `utenti` (
  `id` int(11) NOT NULL,
  `nome` varchar(50) NOT NULL,
  `cognome` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `ruolo` enum('paziente','medico','familiare','admin') NOT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indici per le tabelle scaricate
--

--
-- Indici per le tabelle `assunzioni`
--
ALTER TABLE `assunzioni`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_somministrazione` (`id_somministrazione`);

--
-- Indici per le tabelle `dispositivi`
--
ALTER TABLE `dispositivi`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `codice_seriale` (`codice_seriale`),
  ADD KEY `id_paziente` (`id_paziente`);

--
-- Indici per le tabelle `familiari_pazienti`
--
ALTER TABLE `familiari_pazienti`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id_familiare` (`id_familiare`,`id_paziente`),
  ADD KEY `id_paziente` (`id_paziente`);

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
  ADD KEY `id_paziente` (`id_paziente`),
  ADD KEY `id_assunzione` (`id_assunzione`);

--
-- Indici per le tabelle `medici_pazienti`
--
ALTER TABLE `medici_pazienti`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id_medico` (`id_medico`,`id_paziente`),
  ADD KEY `id_paziente` (`id_paziente`);

--
-- Indici per le tabelle `notifiche`
--
ALTER TABLE `notifiche`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_utente` (`id_utente`);

--
-- Indici per le tabelle `pazienti`
--
ALTER TABLE `pazienti`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id_utente` (`id_utente`);

--
-- Indici per le tabelle `somministrazioni`
--
ALTER TABLE `somministrazioni`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_terapia` (`id_terapia`);

--
-- Indici per le tabelle `terapie`
--
ALTER TABLE `terapie`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_paziente` (`id_paziente`),
  ADD KEY `id_medico` (`id_medico`),
  ADD KEY `id_farmaco` (`id_farmaco`);

--
-- Indici per le tabelle `utenti`
--
ALTER TABLE `utenti`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT per le tabelle scaricate
--

--
-- AUTO_INCREMENT per la tabella `assunzioni`
--
ALTER TABLE `assunzioni`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `dispositivi`
--
ALTER TABLE `dispositivi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `familiari_pazienti`
--
ALTER TABLE `familiari_pazienti`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `farmaci`
--
ALTER TABLE `farmaci`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `feedback`
--
ALTER TABLE `feedback`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `medici_pazienti`
--
ALTER TABLE `medici_pazienti`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `notifiche`
--
ALTER TABLE `notifiche`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `pazienti`
--
ALTER TABLE `pazienti`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `somministrazioni`
--
ALTER TABLE `somministrazioni`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `terapie`
--
ALTER TABLE `terapie`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `utenti`
--
ALTER TABLE `utenti`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Limiti per le tabelle scaricate
--

--
-- Limiti per la tabella `assunzioni`
--
ALTER TABLE `assunzioni`
  ADD CONSTRAINT `assunzioni_ibfk_1` FOREIGN KEY (`id_somministrazione`) REFERENCES `somministrazioni` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Limiti per la tabella `dispositivi`
--
ALTER TABLE `dispositivi`
  ADD CONSTRAINT `dispositivi_ibfk_1` FOREIGN KEY (`id_paziente`) REFERENCES `pazienti` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Limiti per la tabella `familiari_pazienti`
--
ALTER TABLE `familiari_pazienti`
  ADD CONSTRAINT `familiari_pazienti_ibfk_1` FOREIGN KEY (`id_familiare`) REFERENCES `utenti` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `familiari_pazienti_ibfk_2` FOREIGN KEY (`id_paziente`) REFERENCES `pazienti` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Limiti per la tabella `feedback`
--
ALTER TABLE `feedback`
  ADD CONSTRAINT `feedback_ibfk_1` FOREIGN KEY (`id_paziente`) REFERENCES `pazienti` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `feedback_ibfk_2` FOREIGN KEY (`id_assunzione`) REFERENCES `assunzioni` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Limiti per la tabella `medici_pazienti`
--
ALTER TABLE `medici_pazienti`
  ADD CONSTRAINT `medici_pazienti_ibfk_1` FOREIGN KEY (`id_medico`) REFERENCES `utenti` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `medici_pazienti_ibfk_2` FOREIGN KEY (`id_paziente`) REFERENCES `pazienti` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Limiti per la tabella `notifiche`
--
ALTER TABLE `notifiche`
  ADD CONSTRAINT `notifiche_ibfk_1` FOREIGN KEY (`id_utente`) REFERENCES `utenti` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Limiti per la tabella `pazienti`
--
ALTER TABLE `pazienti`
  ADD CONSTRAINT `pazienti_ibfk_1` FOREIGN KEY (`id_utente`) REFERENCES `utenti` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Limiti per la tabella `somministrazioni`
--
ALTER TABLE `somministrazioni`
  ADD CONSTRAINT `somministrazioni_ibfk_1` FOREIGN KEY (`id_terapia`) REFERENCES `terapie` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Limiti per la tabella `terapie`
--
ALTER TABLE `terapie`
  ADD CONSTRAINT `terapie_ibfk_1` FOREIGN KEY (`id_paziente`) REFERENCES `pazienti` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `terapie_ibfk_2` FOREIGN KEY (`id_medico`) REFERENCES `utenti` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `terapie_ibfk_3` FOREIGN KEY (`id_farmaco`) REFERENCES `farmaci` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
