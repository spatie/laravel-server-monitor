const fs = require('fs');
const path = require('path');

class Input {
    constructor() {
        this.input = '';
    }

    put(input) {
        this.input = input.trim('\n');

        return this;
    }

    process() {
        return new Promise((resolve, reject) => fs.readFile(path.resolve(__dirname, 'store.json'), (_, data) => {
            const { expect, output } = JSON.parse(data);

            if (this.input !== expect) {
                reject(`No output specified for command ${this.input}`);
            }

            resolve(output);
        }));
    }
}

module.exports = Input;