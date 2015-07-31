/**
 * Сервер для прослушки командного канала и запуска команд
 */

COMMAND_CHANNEL_NAME = 'command';
SYSTEM_CHANNEL_NAME = 'system';
GATE_CHANNEL_NAME = 'gate';
SYMFONY_CONSOLE_PATH = '../app/console';

var autobahn = require('autobahn');
var exec = require('child_process').exec;

var connection = new autobahn.Connection({
    url: 'ws://localhost:7777/',
    realm: 'kingdom'
});

connection.onopen = function (session) {

    //session.subscribe(COMMAND_CHANNEL_NAME, function onevent(args) {
    //    var cmd = SYMFONY_CONSOLE_PATH + ' --command ' + args[0];
    //
    //    exec(cmd, function (error, stdout, stderr) {
    //        session.publish(COMMAND_CHANNEL_NAME, [stdout]);
    //    });
    //});

    session.publish(SYSTEM_CHANNEL_NAME, ['Gate service is running ...']);

    //TODO: Удаленная команда всем клиентам переподключиться, чтобы гейт подключился к локальным каналам

    //TODO: Отключаться от каналов, когда из них выходят клиенты

    session.register(GATE_CHANNEL_NAME, function (args) {
        var data = args[0];
        var localChannelName = 'character.' + data.hash;

        var isLocalChannelSubscribed = session.subscriptions.some(function(subscription) {
            return subscription[0].topic == localChannelName;
        });

        if (!isLocalChannelSubscribed) {
            session.subscribe(localChannelName, function (args) {
                var data = args[0];
                var command = data.command;
                var commandArguments = data.arguments;

                if (command) {
                    console.log('[' + localChannelName + '] [команда]: ' + command);

                    //TODO: Запуск симфони-команды
                    if (command == 'north') {
                        session.publish(localChannelName, ['Вы отправились на север']);
                        session.publish(localChannelName, [{map: {a1: 1, a2: 2, a3: 3}}]);
                    } else if (command == 'south') {
                        session.publish(localChannelName, ['Вы отправились на юг']);
                        session.publish(localChannelName, [{map: {a1: 3, a2: 2, a3: 1}}]);
                    } else if (command == 'chat') {
                        session.subscriptions.forEach(function(subscription) {
                            session.publish(subscription[0].topic, [{chat: commandArguments}]);
                        });
                    }
                } else {
                    console.log('[' + localChannelName + '] [чат]: ' + data);
                }
            });
        }

        return data;
    });
};

connection.open();
