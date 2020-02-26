<?php
/**
 * @file
 * Verwaltet MySQL-Zugriffe
 * @brief
 * Verwaltet MySQL-Zugriffe
 *
 * @author    Stefan Piffl, Steven Tappert
 * @date    2013-04-09, 2020-02-26
 * @version    1.10
 *
 * @par "2013-08-30 SPi MySQL-Kodierung in UTF-8 geändert"
 * @par "2013-07-26 SPi Dateikodierung in UTF-8 geändert"
 * @par "2020-02-26 Fixed PHP7 types & code style"
 */


/**
 * @brief Verwaltet MySQL-Zugriffe
 */
class SQL
{
    /** Datenbank-Handle */
    /** @var mysqli|bool */
    protected $dbHandle;
    protected $log;

    /**
     * @var array
     */
    private $debug = false;

    /**
     * @brief Initialisiere die MySQL-Klasse
     */
    public function __construct()
    {
        $this->dbHandle = false;
        if (defined('DEBUG_MODE') && DEBUG_MODE)
            $this->debug = array();

    }

    /**
     * @brief Holt / Erstellt eine aktuelle Instanz der Klasse
     */
    public static function & current()
    {
        static $instance = null;
        return $instance = (empty($instance)) ? new self() : $instance;
    }

    /**
     * @brief Finalisiere die MySQL-Klasse
     */
    public function __destruct()
    {
        $this->disconnect();
    }

    /**
     * @brief Trennt Verbindung zur Datenbank
     * @return bool
     */
    public function disconnect(): bool
    {
        if ($this->dbHandle && @mysqli_close($this->dbHandle)) {
            $this->dbHandle = false;
            return true;
        }

        return false;
    }

    /**
     * @brief Verbindung zur Datenbank
     * @param $address string string IP-Adresse/Hostname des MySQL-Servers
     * @param $user string string MySQL-Benutzername
     * @param $pwd string string MySQL-Passwort
     * @param $db string string MySQL-Datenbank
     * @return bool
     */
    public function connect($address, $user, $pwd, $db): bool
    {
        $this->dbHandle = @mysqli_connect($address, $user, $pwd);
        if ($this->dbHandle) {
            mysqli_set_charset($this->dbHandle, 'utf8');
            return mysqli_select_db($this->dbHandle, $db);
        }

        return false;
    }

    /**
     * @brief Gibt das Datenbank-Handle zurück, wenn verbunden
     * @return bool
     */
    public function isConnected()
    {
        return $this->dbHandle;
    }

    /**
     * @brief Gibt die Anzahl der betroffenen Datensätze zurück
     * @return int
     */
    public function affectedRows(): int
    {
        return $this->dbHandle->affected_rows;
    }

    /**
     * @brief Gibt die insertID der letzten Anfrage zurück
     * @return mixed integer insertID der letzten Anfrage
     */
    public function insertID()
    {
        return $this->dbHandle->insert_id;
    }

    /**
     * @brief Gibt die zu erwartende insertID zurück
     * @param $table string string MySQL-Tabelle
     * @return int insertID der nächsten Anfrage
     */
    public function nextInsertID($table): int
    {
        return 1 + $this->fetch("SELECT COUNT( LAST_INSERT_ID() ) AS `count` FROM `" . $db->escape($table) . '`', 'count');
    }

    /**
     * @brief Führt eine Datenbank-Abfrage aus und ein Query->Fetch auf das Resultat;
     * @param $query string Datenbank-Abfrage
     * @param $specific string Tabellenfeld
     * @return array Ergebnis der Abfrage
     */
    function fetch($query, $specific = NULL)
    {
        $q = $this->query($query);
        if ($q) {
            return $q->fetch($specific);
        }

        return null;
    }

    /**
     * @brief Führt eine Datenbank-Abfrage aus
     * @param $query string Datenbank-Abfrage
     * @return mixed true or false or resource or object
     */
    public function query($query)
    {
        if (!$query) {
            return false;
        }

        $return = false;
        if (defined('DEBUG_MODE') && DEBUG_MODE) // Debug-Logs
        {
            $startTime = microtime(true);
        }

        $resource = mysqli_query($this->dbHandle, $query);

        if (defined('DEBUG_MODE') && DEBUG_MODE) {
            $endTime = microtime(true);
            $elapsed = $endTime - $startTime;
            $elapsed *= 1000;

            $elapsed = number_format($elapsed, 5, '.', '') . 'µs';
        }

        // Rückgabewert deuten
        if (is_object($resource)) {
            // Resource (Standardfall SELECT): Neues Query-Objekt erzeugen
            return new Query($resource);
        }

        if ($resource) {
            // Ansonsten direkt Inhalt wiedergeben (true bei INSERT, UPDATE, usw)
            return $resource;
        }

        // Nichts zurückgegeben? Anfrage fehlerhaft, gib Fehler aus
        error_log($query . "\n" . $this->error());
        return false;
    }

    /**
     * @brief Hole Fehlermeldung der Datenbank
     * @return string Fehlermeldung
     */
    public function error(): string
    {
        return $this->dbHandle->error;
    }

    /**
     * @brief Escaped einen String
     * @param $string string Wert
     * @return string Escapter Wert
     */
    public function escape($string): string
    {
        return mysqli_real_escape_string($this->dbHandle, $string);
    }

}


/**
 * @brief Verkörpert das Ergebnis einer bestimmten Datenbankabfrage
 */
class Query
{
    /** Datenbankabfrage */
    /** @var mysqli_result */
    protected $query;

    /**
     * @brief Initialisiere die Query-Klasse
     * @param $query object Datenbankabfrage
     */
    public function __construct($query)
    {
        $this->query = $query;
    }


    /**
     * @brief Gibt die Anzahl der Zeilen der Abfrage zurück
     * @return integer Anzahl der Datensätze
     */
    public function numRows(): int
    {
        return $this->query->num_rows;
    }


    /**
     * @brief Holt eine / alle Zeilen oder eine bestimmte Spalte einer Zeile
     * @param $specific mixed Art der Rückgabe
     * @return array Inhalt der Datensätze
     */
    public function fetch($specific = NULL)
    {
        if (is_string($specific)) {
            // Bestimmte Spalte der nächsten Zeile zurückgeben
            $result = mysqli_fetch_assoc($this->query);
            return $result[$specific];
        } elseif (isset($specific)) {
            // Gesamte nächste Zeile als assoziatives Array zurückgeben
            return mysqli_fetch_assoc($this->query);
        } else {
            // Alle Zeilen als Array ausgeben (assoziative Arrays in numerischem Array)
            $results = array();
            for ($i = 0; $i < $this->query->num_rows; $i++)
                $results[] = mysqli_fetch_assoc($this->query);

            return $results;
        }
    }


    /**
     * @brief Finalisiere die MySQL-Klasse
     */
    public function __destruct()
    {
        mysqli_free_result($this->query);
    }

}
