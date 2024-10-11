<template>
    <div>
    <div class="card">
        <div class="card-header">
            <div>Reports Summary
                <button @click="exportCsv" class="btn btn-outline-success btn-sm float-end me-1" title="Export Claims"><i class="bi bi-filetype-csv"></i></button>
            </div>
        </div>

        <div class="card-body">
            <div class="row g-3 mb-3">
                <div class="col-md-4">
                    <label class="form-label">From:</label>
                    <Input type="date" :min="py.start_date" :max="py.end_date" placeholder="YYYY-MM-DD"
                           class="form-control" v-model="fromDate"/>
                </div>
                <div class="col-md-4">
                    <label class="form-label">To:</label>
                    <Input type="date" :min="py.start_date" :max="py.end_date" placeholder="YYYY-MM-DD"
                           class="form-control" v-model="toDate"/>
                </div>
                <div class="col-md-2">
                    <label class="form-label">&nbsp;</label>
                    <button type="button" class="btn btn-success w-100" @click="submitForm">Refresh</button>
                </div>
                <div v-if="toDate !== $getFormattedDate() || fromDate !== $getFormattedDate()" class="col-md-2">
                    <label class="form-label">&nbsp;</label>
                    <button type="button" class="btn btn-success w-100" @click="clearForm">Clear</button>
                </div>
            </div>

            <div v-if="reportData != null && reportData.publicReport != null" class="table-responsive pb-3">
                <table id="summaryReportTbl" class="table table-striped">
                    <thead>
                    <tr>
                        <th scope="col">Institution</th>
                        <th scope="col">Total Allocation</th>
                        <th scope="col">Total Claimed</th>
                        <th scope="col">Total Hold</th>
                    </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(row, k, i) in reportData.publicReport.instList">
                            <td>&nbsp;&nbsp;{{ k }}</td>
                            <td>${{ $formatNumberWithCommas(row.total) }}</td>
                            <td>${{ $formatNumberWithCommas(row.Claimed) }}</td>
                            <td>${{ $formatNumberWithCommas(row.Hold) }}</td>
                        </tr>
                    </tbody>

                    <tfoot>
                    <tr>
                        <th scope="col">Grand Total</th>
                        <th scope="col">${{ $formatNumberWithCommas(reportData.publicReport.total) }}</th>
                        <th scope="col">${{ $formatNumberWithCommas(reportData.publicReport.Claimed) }}</th>
                        <th scope="col">${{ $formatNumberWithCommas(reportData.publicReport.Hold) }}</th>
                    </tr>
                    </tfoot>
                </table>
            </div>

        </div>

    </div>
    </div>

</template>
<script>
import {Link} from '@inertiajs/vue3';
import Input from '@/Components/Input.vue';

export default {
    name: 'ReportsSummary',
    components: {
        Input, Link
    },
    props: {
        py: Object|null,
    },
    data() {
        return {
            fromDate: '',
            toDate: '',
            reportData: ''
        }
    },
    methods: {
        exportCsv: function (){
            const table = document.getElementById("summaryReportTbl");

            // Initialize CSV string
            let csv = "";

            // Iterate through table rows
            for (let i = 0; i < table.rows.length; i++) {
                const row = table.rows[i];

                // Iterate through table cells
                for (let j = 0; j < row.cells.length; j++) {
                    // Append cell value to CSV string
                    const cellValue = this.cleanCellValue(row.cells[j].innerHTML);
                    csv += '"' + cellValue.replace(/"/g, '""') + '",';
                }

                // Remove trailing comma and add line break
                csv += "\n";
            }

            // Trigger file download
            this.downloadCSV(csv, "table_data.csv");
        },
        cleanCellValue: function(value) {
            // Remove HTML tags and entities using regular expression
            return value.replace(/<!--|--!?>/g, '').replace(/&nbsp;/g, '').trim();
        },
        downloadCSV: function(csv, filename) {
            const blob = new Blob([csv], { type: "text/csv;charset=utf-8;" });
            if (navigator.msSaveBlob) {
                // For IE
                navigator.msSaveBlob(blob, filename);
            } else {
                const link = document.createElement("a");
                if (link.download !== undefined) {
                    // For other browsers
                    const url = URL.createObjectURL(blob);
                    link.setAttribute("href", url);
                    link.setAttribute("download", filename);
                    link.style.visibility = "hidden";
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                }
            }
        },
        clearForm: function () {
            this.toDate = this.py.end_date;
            this.fromDate = this.py.start_date;
            this.submitForm();
        },

        submitForm: function () {
            let vm = this;
            let data = {
                from_date: this.fromDate,
                to_date: this.toDate,
            }
            axios.post('/ministry/reports/summary', data)
                .then(function (response) {
                    vm.reportData = response.data.body;
                })
                .catch(function (error) {
                    // handle error
                    console.log(error);
                });
        }
    },
    mounted() {
        this.toDate = this.py.end_date;
        this.fromDate = this.py.start_date;
        this.submitForm();
    }
}

</script>
