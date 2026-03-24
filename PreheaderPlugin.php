<?php
/**
 * PreheaderPlugin
 *
 * Fügt dem Kampagnen-Formular ein "Preheader"-Feld hinzu.
 * Redakteure können einen Preheadertext eingeben, ohne das HTML zu bearbeiten.
 *
 * Im Template den Marker [PREHEADER] an der gewünschten Stelle platzieren:
 *
 *   <span style="display:none;max-height:0;overflow:hidden;">
 *     [PREHEADER]
 *   </span>
 *
 * Das Plugin ersetzt [PREHEADER] beim Versand durch den eingegebenen Text.
 */
class PreheaderPlugin extends phplistPlugin
{
    public $name        = 'Preheader Plugin';
    public $version     = '1.0.0';
    public $authors     = 'Ihr Name';
    public $description = 'Ermöglicht das Hinterlegen eines Preheadertexts pro Kampagne.';
    public $enabled     = 1;

    // Eigene DB-Tabelle für Preheader-Texte
    var $DBstruct = array(
        'preheader' => array(
            'id'        => array('integer not null primary key auto_increment', 'ID'),
            'messageid' => array('integer not null',                            'Kampagnen-ID'),
            'preheader' => array('text',                                        'Preheadertext'),
            'unique_1'  => array('unique_messageid (messageid)',                ''),
        ),
    );

    public function dependencyCheck()
    {
        return [
            'phpList 3.3.0 or later' => version_compare(VERSION, '3.3.0') >= 0,
        ];
    }

    // ----------------------------------------------------------------
    // Tab-Titel im Kampagnen-Formular
    // ----------------------------------------------------------------
    public function sendMessageTabTitle($messageid = 0)
    {
        return 'Preheader';
    }

    // ----------------------------------------------------------------
    // Tab-Inhalt: Eingabefeld für den Preheadertext
    // ----------------------------------------------------------------
    public function sendMessageTab($messageid = 0, $messagedata = [])
    {
        // Vorhandenen Wert aus DB laden (beim Bearbeiten einer Kampagne)
        $current = '';
        if ($messageid > 0) {
            $row = Sql_Fetch_Array_Query(
                sprintf(
                    'SELECT preheader FROM %s WHERE messageid = %d',
                    $this->tables['preheader'],
                    (int) $messageid
                )
            );
            if (!empty($row['preheader'])) {
                $current = htmlspecialchars($row['preheader']);
            }
        }

        return '
        <div class="field">
            <label for="preheader_text">
                Preheadertext
                <small style="display:block;color:#666;font-weight:normal;">
                    Kurzer Vorschautext, der in E-Mail-Clients neben dem Betreff erscheint
                    (empfohlen: 40–130 Zeichen). Platzieren Sie <code>[PREHEADER]</code>
                    an der gewünschten Stelle in Ihrem Template.
                </small>
            </label>
            <input
                type="text"
                id="preheader_text"
                name="preheader_text"
                value="' . $current . '"
                maxlength="200"
                style="width:100%;"
                placeholder="z. B.: Jetzt 20% Rabatt sichern – nur bis Sonntag!"
            >
            <small id="preheader_counter" style="color:#999;">0 / 130 Zeichen</small>
        </div>
        <script>
        (function() {
            var input   = document.getElementById("preheader_text");
            var counter = document.getElementById("preheader_counter");
            function update() {
                var len = input.value.length;
                counter.textContent = len + " / 130 Zeichen";
                counter.style.color = len > 130 ? "#c00" : "#999";
            }
            input.addEventListener("input", update);
            update();
        })();
        </script>
        ';
    }


    // ----------------------------------------------------------------
    // Speichern: wird aufgerufen, wenn die Kampagne gespeichert wird
    // ----------------------------------------------------------------
    public function sendMessageTabSave($messageid = 0, $data = [])
    {
        if ($messageid <= 0) {
            return;
        }

        if (isset($_POST['preheader_text'])) {
            $preheader = trim($_POST['preheader_text']);

            // UPSERT: vorhandenen Eintrag ersetzen oder neu anlegen
            Sql_Query(
                sprintf(
                    'REPLACE INTO %s (messageid, preheader) VALUES (%d, "%s")',
                    $this->tables['preheader'],
                    (int) $messageid,
                    sql_escape($preheader)
                )
            );
        }
    }

    // ----------------------------------------------------------------
    // Versand: [PREHEADER] im HTML durch den gespeicherten Text ersetzen
    // ----------------------------------------------------------------
// Dieser Hook verarbeitet die fertige Mail (Template + Content)
public function parseMessage($messageid, $content, $destination, $userdata = null)
{
    return $this->injectPreheader($messageid, $content);
}

// Sicherheits-Fallback für HTML
public function parseOutgoingHTMLMessage($messageid, $content, $destination, $userdata = null)
{
    return $this->injectPreheader($messageid, $content);
}

private function injectPreheader($messageid, $content)
{
    if ($messageid <= 0) return $content;

    // Datenbank-Abfrage
    $row = Sql_Fetch_Array_Query(sprintf(
        'SELECT preheader FROM %s WHERE messageid = %d',
        $this->tables['preheader'],
        (int) $messageid
    ));

    $text = !empty($row['preheader']) ? trim($row['preheader']) : '';

    if ($text !== '') {
        $cleanText = htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
        // Das Padding sorgt dafür, dass die Inbox gefüllt wird
        $replacement = $cleanText . str_repeat('&nbsp;&zwnj; ', 200);
    } else {
        $replacement = '';
    }

    // Wir nutzen eine aggressive Ersetzung im gesamten Dokument
    return str_replace('[PREHEADER]', $replacement, $content);
}
}