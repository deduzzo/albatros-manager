<?php

use yii\db\Migration;

/**
 * Inserisce docenti di esempio con tag associati per testing
 */
class m240131_100005_insert_sample_docenti extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // Inserisci docenti di esempio
        $docenti = [
            [
                'RSSMRA80A01F205X', 'Rossi', 'Mario', '1980-01-01', 'Milano',
                'Via Roma 123', '20100', 'Milano', 'MI', '+39 02 1234567',
                'mario.rossi@example.com', 'Laurea in Informatica',
                'Sviluppo web, PHP, JavaScript, React', '12345678901',
                'IT60X0542811101000000123456', 'Docente senior con 15 anni di esperienza'
            ],
            [
                'BNCLCA85E41H501Y', 'Bianchi', 'Luca', '1985-05-01', 'Roma',
                'Via Appia 45', '00179', 'Roma', 'RM', '+39 06 9876543',
                'luca.bianchi@example.com', 'Laurea in Ingegneria Informatica',
                'Cloud computing, AWS, DevOps, Docker', '98765432109',
                'IT60X0542811101000000654321', 'Specialista cloud e infrastrutture'
            ],
            [
                'VRDGNN90L52H501Z', 'Verdi', 'Gianna', '1990-07-12', 'Napoli',
                'Via Toledo 78', '80134', 'Napoli', 'NA', '+39 081 5551234',
                'gianna.verdi@example.com', 'PhD in Data Science',
                'Machine Learning, Python, AI, Data Analysis', '11223344556',
                'IT60X0542811101000000112233', 'Ricercatrice in AI e Machine Learning'
            ],
            [
                'NRIPLA88D15D612W', 'Neri', 'Paolo', '1988-04-15', 'Firenze',
                'Via dei Calzaiuoli 12', '50122', 'Firenze', 'FI', '+39 055 4443322',
                'paolo.neri@example.com', 'Laurea in Economia e Informatica',
                'Project Management, Agile, Scrum', null,
                null, 'Project Manager certificato PMP e Scrum Master'
            ],
            [
                'GLLFRC92S55G273V', 'Galli', 'Francesca', '1992-11-15', 'Bologna',
                'Via Indipendenza 90', '40121', 'Bologna', 'BO', '+39 051 6667788',
                'francesca.galli@example.com', 'Laurea in Design e Comunicazione',
                'UX/UI Design, Figma, Adobe XD, HTML/CSS', '55667788990',
                'IT60X0542811101000000556677', 'Designer con focus su user experience'
            ],
            [
                'MRCGPP87B28C351A', 'Marchetti', 'Giuseppe', '1987-02-28', 'Torino',
                'Corso Francia 45', '10138', 'Torino', 'TO', '+39 011 3334455',
                'giuseppe.marchetti@example.com', 'Laurea in Ingegneria del Software',
                'Java, Spring, Microservizi, Kubernetes', '44556677889',
                'IT60X0542811101000000445566', 'Architetto software senior'
            ],
            [
                'CNTSFN95A42L219B', 'Conti', 'Stefania', '1995-01-02', 'Venezia',
                'Campo Santa Margherita 5', '30123', 'Venezia', 'VE', '+39 041 2223344',
                'stefania.conti@example.com', 'Laurea in Cybersecurity',
                'Sicurezza informatica, Penetration Testing, Ethical Hacking', '33445566778',
                'IT60X0542811101000000334455', 'Esperta in sicurezza informatica'
            ],
            [
                'FRRLSS83C18E625C', 'Ferrari', 'Alessio', '1983-03-18', 'Genova',
                'Via XX Settembre 33', '16121', 'Genova', 'GE', '+39 010 8889900',
                'alessio.ferrari@example.com', 'Laurea in Informatica',
                'Database, SQL, PostgreSQL, MongoDB, Redis', '22334455667',
                'IT60X0542811101000000223344', 'DBA con esperienza enterprise'
            ],
        ];

        foreach ($docenti as $d) {
            $this->insert('docente', [
                'codice_fiscale' => $d[0],
                'cognome' => $d[1],
                'nome' => $d[2],
                'data_nascita' => $d[3],
                'luogo_nascita' => $d[4],
                'indirizzo' => $d[5],
                'cap' => $d[6],
                'citta' => $d[7],
                'provincia' => $d[8],
                'telefono' => $d[9],
                'email' => $d[10],
                'titolo_studio' => $d[11],
                'specializzazioni' => $d[12],
                'partita_iva' => $d[13],
                'iban' => $d[14],
                'note' => $d[15],
            ]);
        }

        // Associa tag ai docenti
        // Mario Rossi - PHP, JavaScript, React, Laravel, MySQL
        $tagsRossi = ['php', 'javascript', 'react', 'laravel', 'mysql'];
        foreach ($tagsRossi as $codice) {
            $tagId = (new \yii\db\Query())->select('id')->from('tag_curriculum')->where(['codice' => $codice])->scalar();
            if ($tagId) {
                $this->insert('docente_tag', [
                    'docente_codice_fiscale' => 'RSSMRA80A01F205X',
                    'tag_id' => $tagId,
                    'livello' => rand(3, 5),
                ]);
            }
        }

        // Luca Bianchi - AWS, Docker, Kubernetes, Linux, CI/CD
        $tagsBianchi = ['aws', 'docker', 'kubernetes', 'linux', 'cicd'];
        foreach ($tagsBianchi as $codice) {
            $tagId = (new \yii\db\Query())->select('id')->from('tag_curriculum')->where(['codice' => $codice])->scalar();
            if ($tagId) {
                $this->insert('docente_tag', [
                    'docente_codice_fiscale' => 'BNCLCA85E41H501Y',
                    'tag_id' => $tagId,
                    'livello' => rand(4, 5),
                ]);
            }
        }

        // Gianna Verdi - Python, AI, TDD
        $tagsVerdi = ['python', 'ai', 'tdd'];
        foreach ($tagsVerdi as $codice) {
            $tagId = (new \yii\db\Query())->select('id')->from('tag_curriculum')->where(['codice' => $codice])->scalar();
            if ($tagId) {
                $this->insert('docente_tag', [
                    'docente_codice_fiscale' => 'VRDGNN90L52H501Z',
                    'tag_id' => $tagId,
                    'livello' => 5,
                ]);
            }
        }

        // Paolo Neri - Agile, PMP, Scrum, Leadership, Team Working
        $tagsNeri = ['agile', 'pmp', 'scrum', 'leadership', 'teamwork'];
        foreach ($tagsNeri as $codice) {
            $tagId = (new \yii\db\Query())->select('id')->from('tag_curriculum')->where(['codice' => $codice])->scalar();
            if ($tagId) {
                $this->insert('docente_tag', [
                    'docente_codice_fiscale' => 'NRIPLA88D15D612W',
                    'tag_id' => $tagId,
                    'livello' => rand(4, 5),
                ]);
            }
        }

        // Francesca Galli - UX, HTML/CSS, React
        $tagsGalli = ['ux', 'html-css', 'react'];
        foreach ($tagsGalli as $codice) {
            $tagId = (new \yii\db\Query())->select('id')->from('tag_curriculum')->where(['codice' => $codice])->scalar();
            if ($tagId) {
                $this->insert('docente_tag', [
                    'docente_codice_fiscale' => 'GLLFRC92S55G273V',
                    'tag_id' => $tagId,
                    'livello' => rand(4, 5),
                ]);
            }
        }

        // Giuseppe Marchetti - Java, Spring, Docker, Kubernetes
        $tagsMarchetti = ['java', 'spring', 'docker', 'kubernetes'];
        foreach ($tagsMarchetti as $codice) {
            $tagId = (new \yii\db\Query())->select('id')->from('tag_curriculum')->where(['codice' => $codice])->scalar();
            if ($tagId) {
                $this->insert('docente_tag', [
                    'docente_codice_fiscale' => 'MRCGPP87B28C351A',
                    'tag_id' => $tagId,
                    'livello' => 5,
                ]);
            }
        }

        // Stefania Conti - Cybersecurity, Linux
        $tagsConti = ['cybersecurity', 'linux'];
        foreach ($tagsConti as $codice) {
            $tagId = (new \yii\db\Query())->select('id')->from('tag_curriculum')->where(['codice' => $codice])->scalar();
            if ($tagId) {
                $this->insert('docente_tag', [
                    'docente_codice_fiscale' => 'CNTSFN95A42L219B',
                    'tag_id' => $tagId,
                    'livello' => 5,
                ]);
            }
        }

        // Alessio Ferrari - MySQL, PostgreSQL, MongoDB, Redis, SQL
        $tagsFerrari = ['mysql', 'postgresql', 'mongodb', 'redis', 'sql'];
        foreach ($tagsFerrari as $codice) {
            $tagId = (new \yii\db\Query())->select('id')->from('tag_curriculum')->where(['codice' => $codice])->scalar();
            if ($tagId) {
                $this->insert('docente_tag', [
                    'docente_codice_fiscale' => 'FRRLSS83C18E625C',
                    'tag_id' => $tagId,
                    'livello' => 5,
                ]);
            }
        }

        // Inserisci alcune valutazioni di esempio
        // Prima verifichiamo se esistono moduli (potrebbero non esistere ancora)
        // Saltiamo le valutazioni per ora, verranno aggiunte quando ci saranno i moduli

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // Rimuovi tag associati
        $this->delete('docente_tag', ['docente_codice_fiscale' => [
            'RSSMRA80A01F205X', 'BNCLCA85E41H501Y', 'VRDGNN90L52H501Z',
            'NRIPLA88D15D612W', 'GLLFRC92S55G273V', 'MRCGPP87B28C351A',
            'CNTSFN95A42L219B', 'FRRLSS83C18E625C',
        ]]);

        // Rimuovi docenti
        $this->delete('docente', ['codice_fiscale' => [
            'RSSMRA80A01F205X', 'BNCLCA85E41H501Y', 'VRDGNN90L52H501Z',
            'NRIPLA88D15D612W', 'GLLFRC92S55G273V', 'MRCGPP87B28C351A',
            'CNTSFN95A42L219B', 'FRRLSS83C18E625C',
        ]]);

        return true;
    }
}
