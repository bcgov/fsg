
export const globalMixins = {
    methods: {
        $getFormattedDate() {
            const today = new Date();
            const year = today.getFullYear();
            const month = String(today.getMonth() + 1).padStart(2, '0');
            const day = String(today.getDate()).padStart(2, '0');
            return `${year}-${month}-${day}`;
        },
        $getProgramNameFromGuid(programs, program_guid) {
            let name = '';
            programs.forEach(program => {
                if (program.guid === program_guid) {
                    name = program.program_name;
                }
            });
            return name;
        },
        $getYesNo(value) {
            return value == true ? 'Yes' : 'No';
        },

        $formatNumberWithCommas(value) {
            return new Intl.NumberFormat('en-US', {
                minimumFractionDigits: 0,
                maximumFractionDigits: 2
            }).format(value);
        },

        $formatDate: function (value) {
            if(value !== undefined && value !== ''){
                let date = value.split("T");
                let time = date[1].split(".");

                return date[0];
            }
            return value;
        },
        $amountPlusPyFee: function(amount, pyFee){
            return parseFloat(amount) + (parseFloat(amount) / parseFloat(pyFee));
        }
    }
};
