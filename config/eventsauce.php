<?php

return [
    /*
     * Types, commands and events can be generated starting from a yaml file.
     * Here you can specify the input and the output of the code generation.
     *
     * More info on code generation here:
     * https://eventsauce.io/docs/getting-started/create-events-and-commands
     */
    'code_generation' => [
        [
            'input_yaml_file' => null,
            'output_file' => null,
        ],
    ],

    /*
     * This connection name will be used to store messages. When
     * set to null the default connection will be used.
     */
    'connection' => env('EVENTSAUCE_CONNECTION'),

    /*
     * The default table name, used to store messages.
     */
    'table' =>  env('EVENTSAUCE_TABLE', 'domain_messages'),

    /*
     * The default table name, used to store snapshots.
     */
    'snapshot_table' => env('EVENTSAUCE_SNAPSHOT_TABLE', 'snapshots'),

    /*
     * This class will be used to store messages.
     *
     * You may change this to any class that implements
     * \EventSauce\EventSourcing\MessageRepository
     */
    'message_repository' => \Idunis\EventSauce\MessageRepository\MemoryMessageRepository::class,

    /*
     * This class will be used to store snapshots.
     *
     * You may change this to any class that implements
     * EventSauce\EventSourcing\Snapshotting\SnapshotRepository
     */
    'snapshot_repository' => \Idunis\EventSauce\MessageRepository\MemorySnapshotRepository::class,

];
