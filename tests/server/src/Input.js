const ENTER = Buffer.from('0d', 'hex');
const EXIT = Buffer.from('03', 'hex');

class Input {
    constructor() {
        this.input = '';
        this.commands = [];
    }

    put(input) {
        this.input = input.trim('\n');

        this.process();
    }

    process() {
        const command = this.commands.find(([command]) => this.input === command);

        const output = command ?
            command[1] :
            () => 'No command specified for `' + this.input + '`';

        console.log(output());

        this.input = '';
    }

    exit() {
    }

    hears(command, handler) {
        this.commands.push([command, handler]);

        return this;
    }
}

module.exports = Input;