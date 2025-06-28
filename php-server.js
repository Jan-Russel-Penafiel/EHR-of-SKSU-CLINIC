const { spawn } = require('child_process');

exports.handler = async (event, context) => {
    return new Promise((resolve, reject) => {
        const php = spawn('php', ['-r', `echo "Hello from PHP!";`]);

        php.stdout.on('data', (data) => {
            resolve({
                statusCode: 200,
                body: data.toString()
            });
        });

        php.stderr.on('data', (data) => {
            reject({
                statusCode: 500,
                body: data.toString()
            });
        });
    });
};
