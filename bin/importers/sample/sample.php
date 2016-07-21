<?php

########################################################################################################################
# CONFIG
########################################################################################################################

$CONFIG = [];
$CONFIG['dbinfo'] = [
    'host'     => 'localhost',
    'port'     => '3306',
    'user'     => 'root',
    'password' => '',
    'dbname'   => 'deskpro',
    'driver'   => 'pdo_mysql',
];

########################################################################################################################
# Do not edit below this line
########################################################################################################################

$faker = \Faker\Factory::create();

/** @var \Application\ImportBundle\ScriptHelper\OutputHelper $output */
/** @var \Application\ImportBundle\ScriptHelper\WriteHelper $writer */
/** @var \Application\ImportBundle\ScriptHelper\DbHelper $db */

$db->setCredentials($CONFIG['dbinfo']);

$output->startSection('People');
for ($i = 0; $i < 10; $i++) {
    for ($j = 1; $j <= 10; $j++) {
        $oid = $i * 10 + $j;
        $output->info('Export person'.$oid);
        $writer->writePerson($oid, [
            'name'          => $faker->name,
            'emails'        => [$faker->email],
            'contact_data'  => [
                'facebook' => [
                    ['url' => $faker->url],
                ]
            ],
            'custom_fields' => [
                ['name' => 'name', 'value' => 'text'],
            ],
        ]);
    }

    $writer->advancePage();
}

$output->startSection('Organizations');
for ($j = 1; $j < 10; $j++) {
    $output->info('Export organization '.$j);
    $writer->writeOrganization($j, [
        'name'   => $faker->title,
        'labels' => [$faker->word, $faker->word],
    ]);
}

$output->startSection('TicketCustomDef');
for ($j = 1; $j < 10; $j++) {
    $writer->writeTicketCustomDef($j, [
        'title'       => $faker->title,
        'widget_type' => 'checkbox',
        'choices'     => [
            ['title' => 'Choice 1'],
            ['title' => 'Choice 2'],
        ],
    ]);
}

$output->startSection('Ticket');
for ($j = 1; $j < 10; $j++) {
    $writer->writeTicket($j, [
        'subject'  => $faker->title,
        'person'   => 1,
        'agent'    => 2,
        'labels'   => [$faker->word, $faker->word],
        'status'   => 'awaiting_agent',
        'messages' => [
            [
                'oid'         => 1,
                'person'      => 1,
                'message'     => 'ticket message',
                'format'      => 'text',
                'attachments' => [
                    [
                        'blob_url'     => $faker->imageUrl(),
                        'file_name'    => $faker->title,
                        'content_type' => $faker->fileExtension,
                        'person'       => 1,
                    ]
                ],
            ],
        ],
    ]);
}

$output->startSection('News');
$pager = $db->getPager('select * from news');

while ($rawNews = $pager->next()) {
    foreach ($rawNews as $n) {
        $writer->writeNews($n['id'], [
            'title'    => $n['title'],
            'content'  => $n['content'],
            'language' => $n['language_id'],
            'status'   => $n['status'],
            'person'   => $n['person_id'],
        ]);
        $writer->printLastModel();
    }
}

$output->finishProcess();
