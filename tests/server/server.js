const createServer = require('./src/createServer');

createServer().then(server => {

    server.hears(
        'bash -se <<EOF-LARAVEL-SERVER-MONITOR\n' +
        'set -e\n' +
        'df -P .\n' +
        'EOF-LARAVEL-SERVER-MONITOR',
        () =>
            'Filesystem 512-blocks      Used Available Capacity  Mounted on\n' +
            '/dev/disk1  974700800 830137776 144051024    86%    /'
    );
});
