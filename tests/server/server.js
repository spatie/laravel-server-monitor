const fs = require('fs');
const path = require('path');
const ssh2 = require('ssh2');
const Input = require('./Input');

const hostKeys = [
    fs.readFileSync(path.resolve(__dirname, 'host.key'))
];

const createServer = () => new Promise((resolve) => {
    const input = new Input();

    new ssh2.Server(
        { hostKeys },
        client => startSession(client, input).then(() => resolve(input))
    ).listen(65000, '127.0.0.1', function () {
        console.log('Listening on port ' + this.address().port);
    });
});

const startSession = (client, inputHandler) => new Promise((resolve) => {
    client.on('authentication', ctx => ctx.accept());

    client.on('ready', () => {
        client.on('session', accept => {

            resolve();

            accept().on('exec', (accept, _, { command }) => {
                const stream = accept();

                inputHandler.put(command).process()
                    .then((output) => {
                        stream.stdout.write(output);
                        stream.exit(0);
                        stream.close();
                    })
                    .catch((error) => {
                        stream.stderr.write(error);
                        stream.exit(1);
                        stream.close();
                    });
            });
        });
    });
});

createServer();