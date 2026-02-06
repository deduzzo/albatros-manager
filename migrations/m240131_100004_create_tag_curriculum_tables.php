<?php

use yii\db\Migration;

/**
 * Crea le tabelle per il sistema Tag Curriculum:
 * - tag_categoria: categorie dei tag (es. Linguaggi, Framework, Database)
 * - tag_curriculum: tag disponibili (es. PHP, Java, MySQL)
 * - docente_tag: associazione docente-tag
 */
class m240131_100004_create_tag_curriculum_tables extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // Tabella categorie tag
        $this->createTable('tag_categoria', [
            'id' => $this->primaryKey(),
            'nome' => $this->string(50)->notNull()->unique(),
            'descrizione' => $this->string(255)->null(),
            'colore' => $this->string(7)->notNull()->defaultValue('#206bc4'), // Hex color
            'icona' => $this->string(50)->null(), // Tabler icon class
            'ordine' => $this->smallInteger()->notNull()->defaultValue(0),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
            'deleted_at' => $this->timestamp()->null(),
            'created_by' => $this->integer()->null(),
            'updated_by' => $this->integer()->null(),
            'deleted_by' => $this->integer()->null(),
        ]);

        $this->createIndex('idx_tag_categoria_deleted', 'tag_categoria', 'deleted_at');

        // Foreign keys per audit
        $this->addForeignKey('fk_tag_categoria_created_by', 'tag_categoria', 'created_by', 'utente', 'id', 'SET NULL');
        $this->addForeignKey('fk_tag_categoria_updated_by', 'tag_categoria', 'updated_by', 'utente', 'id', 'SET NULL');
        $this->addForeignKey('fk_tag_categoria_deleted_by', 'tag_categoria', 'deleted_by', 'utente', 'id', 'SET NULL');

        // Tabella tag curriculum
        $this->createTable('tag_curriculum', [
            'id' => $this->primaryKey(),
            'categoria_id' => $this->integer()->notNull(),
            'codice' => $this->string(30)->notNull()->unique(), // Codice breve (es. php, java, ai)
            'nome' => $this->string(100)->notNull(), // Nome completo
            'descrizione' => $this->text()->null(), // Descrizione estesa ricercabile
            'keywords' => $this->string(255)->null(), // Keywords per ricerca (es. "ia,ml,machine learning")
            'ordine' => $this->smallInteger()->notNull()->defaultValue(0),
            'attivo' => $this->boolean()->notNull()->defaultValue(true),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
            'deleted_at' => $this->timestamp()->null(),
            'created_by' => $this->integer()->null(),
            'updated_by' => $this->integer()->null(),
            'deleted_by' => $this->integer()->null(),
        ]);

        $this->createIndex('idx_tag_curriculum_categoria', 'tag_curriculum', 'categoria_id');
        $this->createIndex('idx_tag_curriculum_deleted', 'tag_curriculum', 'deleted_at');
        $this->createIndex('idx_tag_curriculum_attivo', 'tag_curriculum', 'attivo');

        $this->addForeignKey('fk_tag_curriculum_categoria', 'tag_curriculum', 'categoria_id', 'tag_categoria', 'id', 'CASCADE');
        $this->addForeignKey('fk_tag_curriculum_created_by', 'tag_curriculum', 'created_by', 'utente', 'id', 'SET NULL');
        $this->addForeignKey('fk_tag_curriculum_updated_by', 'tag_curriculum', 'updated_by', 'utente', 'id', 'SET NULL');
        $this->addForeignKey('fk_tag_curriculum_deleted_by', 'tag_curriculum', 'deleted_by', 'utente', 'id', 'SET NULL');

        // Tabella associazione docente-tag
        $this->createTable('docente_tag', [
            'id' => $this->primaryKey(),
            'docente_codice_fiscale' => $this->char(16)->notNull(),
            'tag_id' => $this->integer()->notNull(),
            'livello' => $this->smallInteger()->null(), // 1-5 livello competenza (opzionale)
            'note' => $this->string(255)->null(),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            'created_by' => $this->integer()->null(),
        ]);

        $this->createIndex('idx_docente_tag_docente', 'docente_tag', 'docente_codice_fiscale');
        $this->createIndex('idx_docente_tag_tag', 'docente_tag', 'tag_id');
        $this->createIndex('idx_docente_tag_unique', 'docente_tag', ['docente_codice_fiscale', 'tag_id'], true);

        $this->addForeignKey('fk_docente_tag_docente', 'docente_tag', 'docente_codice_fiscale', 'docente', 'codice_fiscale', 'CASCADE');
        $this->addForeignKey('fk_docente_tag_tag', 'docente_tag', 'tag_id', 'tag_curriculum', 'id', 'CASCADE');
        $this->addForeignKey('fk_docente_tag_created_by', 'docente_tag', 'created_by', 'utente', 'id', 'SET NULL');

        // Inserisci categorie di default
        $this->batchInsert('tag_categoria', ['nome', 'descrizione', 'colore', 'icona', 'ordine'], [
            ['Linguaggi', 'Linguaggi di programmazione', '#206bc4', 'ti ti-code', 1],
            ['Framework', 'Framework e librerie', '#4299e1', 'ti ti-box', 2],
            ['Database', 'Database e data storage', '#38a169', 'ti ti-database', 3],
            ['Cloud & DevOps', 'Cloud computing e DevOps', '#ed8936', 'ti ti-cloud', 4],
            ['Soft Skills', 'Competenze trasversali', '#9f7aea', 'ti ti-users', 5],
            ['Certificazioni', 'Certificazioni professionali', '#e53e3e', 'ti ti-certificate', 6],
            ['Metodologie', 'Metodologie e pratiche', '#319795', 'ti ti-chart-dots', 7],
            ['Settori', 'Settori di competenza', '#667eea', 'ti ti-building', 8],
        ]);

        // Inserisci tag di default
        $this->batchInsert('tag_curriculum', ['categoria_id', 'codice', 'nome', 'descrizione', 'keywords', 'ordine'], [
            // Linguaggi (categoria_id = 1)
            [1, 'php', 'PHP', 'Linguaggio di scripting server-side', 'php,laravel,symfony', 1],
            [1, 'java', 'Java', 'Linguaggio object-oriented', 'java,spring,jvm', 2],
            [1, 'python', 'Python', 'Linguaggio versatile per web, AI e data science', 'python,django,flask,ml', 3],
            [1, 'javascript', 'JavaScript', 'Linguaggio per web development', 'js,node,react,vue,angular', 4],
            [1, 'csharp', 'C#', 'Linguaggio Microsoft .NET', 'c#,dotnet,.net,asp', 5],
            [1, 'cpp', 'C/C++', 'Linguaggi di sistema', 'c,c++,embedded,systems', 6],
            [1, 'sql', 'SQL', 'Structured Query Language', 'sql,query,database', 7],
            [1, 'html-css', 'HTML/CSS', 'Markup e styling web', 'html,css,sass,less,bootstrap', 8],

            // Framework (categoria_id = 2)
            [2, 'react', 'React', 'Libreria JavaScript per UI', 'react,redux,nextjs', 1],
            [2, 'angular', 'Angular', 'Framework TypeScript di Google', 'angular,typescript,rxjs', 2],
            [2, 'vue', 'Vue.js', 'Framework JavaScript progressivo', 'vue,vuex,nuxt', 3],
            [2, 'laravel', 'Laravel', 'Framework PHP', 'laravel,php,eloquent', 4],
            [2, 'spring', 'Spring', 'Framework Java enterprise', 'spring,springboot,java', 5],
            [2, 'dotnet', '.NET', 'Framework Microsoft', 'dotnet,aspnet,blazor,c#', 6],
            [2, 'nodejs', 'Node.js', 'Runtime JavaScript server-side', 'node,express,npm', 7],

            // Database (categoria_id = 3)
            [3, 'mysql', 'MySQL', 'Database relazionale open source', 'mysql,mariadb,sql', 1],
            [3, 'postgresql', 'PostgreSQL', 'Database relazionale avanzato', 'postgres,postgresql,sql', 2],
            [3, 'mongodb', 'MongoDB', 'Database NoSQL document-based', 'mongo,nosql,document', 3],
            [3, 'oracle', 'Oracle', 'Database enterprise', 'oracle,plsql,enterprise', 4],
            [3, 'sqlserver', 'SQL Server', 'Database Microsoft', 'sqlserver,mssql,tsql', 5],
            [3, 'redis', 'Redis', 'Database in-memory', 'redis,cache,nosql', 6],

            // Cloud & DevOps (categoria_id = 4)
            [4, 'aws', 'AWS', 'Amazon Web Services', 'aws,amazon,cloud,ec2,s3', 1],
            [4, 'azure', 'Azure', 'Microsoft Azure', 'azure,microsoft,cloud', 2],
            [4, 'gcp', 'Google Cloud', 'Google Cloud Platform', 'gcp,google,cloud', 3],
            [4, 'docker', 'Docker', 'Containerizzazione', 'docker,container,compose', 4],
            [4, 'kubernetes', 'Kubernetes', 'Orchestrazione container', 'k8s,kubernetes,container', 5],
            [4, 'cicd', 'CI/CD', 'Continuous Integration/Deployment', 'cicd,jenkins,gitlab,github', 6],
            [4, 'linux', 'Linux', 'Sistemi operativi Linux', 'linux,ubuntu,centos,bash', 7],

            // Soft Skills (categoria_id = 5)
            [5, 'teamwork', 'Team Working', 'Lavoro di squadra', 'team,collaborazione,gruppo', 1],
            [5, 'leadership', 'Leadership', 'Capacità di leadership', 'leader,gestione,management', 2],
            [5, 'communication', 'Comunicazione', 'Capacità comunicative', 'comunicazione,presentazione,public speaking', 3],
            [5, 'problem-solving', 'Problem Solving', 'Risoluzione problemi', 'problem solving,analisi,logica', 4],
            [5, 'teaching', 'Didattica', 'Capacità didattiche', 'insegnamento,formazione,didattica', 5],

            // Certificazioni (categoria_id = 6)
            [6, 'aws-cert', 'AWS Certified', 'Certificazioni Amazon AWS', 'aws,certified,solutions architect', 1],
            [6, 'azure-cert', 'Azure Certified', 'Certificazioni Microsoft Azure', 'azure,certified,microsoft', 2],
            [6, 'cisco', 'Cisco', 'Certificazioni Cisco', 'ccna,ccnp,networking', 3],
            [6, 'pmp', 'PMP', 'Project Management Professional', 'pmp,project management,pmi', 4],
            [6, 'scrum', 'Scrum Master', 'Certificazione Scrum', 'scrum,agile,csm', 5],

            // Metodologie (categoria_id = 7)
            [7, 'agile', 'Agile', 'Metodologie Agile', 'agile,scrum,kanban,sprint', 1],
            [7, 'tdd', 'TDD', 'Test Driven Development', 'tdd,test,unit test', 2],
            [7, 'devops-method', 'DevOps', 'Cultura e pratiche DevOps', 'devops,automation,infrastructure', 3],
            [7, 'ux', 'UX/UI Design', 'User Experience Design', 'ux,ui,design,figma,user experience', 4],

            // Settori (categoria_id = 8)
            [8, 'ai', 'Intelligenza Artificiale', 'AI e Machine Learning', 'ai,ia,ml,machine learning,deep learning', 1],
            [8, 'cybersecurity', 'Cybersecurity', 'Sicurezza informatica', 'security,cyber,ethical hacking', 2],
            [8, 'iot', 'IoT', 'Internet of Things', 'iot,embedded,sensori,arduino', 3],
            [8, 'blockchain', 'Blockchain', 'Tecnologie blockchain', 'blockchain,crypto,web3,smart contract', 4],
            [8, 'ecommerce', 'E-commerce', 'Commercio elettronico', 'ecommerce,shop,magento,woocommerce', 5],
            [8, 'erp', 'ERP/CRM', 'Sistemi gestionali', 'erp,crm,sap,salesforce', 6],
        ]);

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk_docente_tag_created_by', 'docente_tag');
        $this->dropForeignKey('fk_docente_tag_tag', 'docente_tag');
        $this->dropForeignKey('fk_docente_tag_docente', 'docente_tag');
        $this->dropTable('docente_tag');

        $this->dropForeignKey('fk_tag_curriculum_deleted_by', 'tag_curriculum');
        $this->dropForeignKey('fk_tag_curriculum_updated_by', 'tag_curriculum');
        $this->dropForeignKey('fk_tag_curriculum_created_by', 'tag_curriculum');
        $this->dropForeignKey('fk_tag_curriculum_categoria', 'tag_curriculum');
        $this->dropTable('tag_curriculum');

        $this->dropForeignKey('fk_tag_categoria_deleted_by', 'tag_categoria');
        $this->dropForeignKey('fk_tag_categoria_updated_by', 'tag_categoria');
        $this->dropForeignKey('fk_tag_categoria_created_by', 'tag_categoria');
        $this->dropTable('tag_categoria');

        return true;
    }
}
