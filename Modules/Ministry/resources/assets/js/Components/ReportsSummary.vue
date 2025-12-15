<template>
    <div>
        <div class="card">
            <div class="card-header">
                <div>Reports Summary
                <button @click="exportCsv" class="btn btn-outline-success btn-sm float-end me-1" title="Export Claims"><i class="bi bi-filetype-csv"></i></button>
            </div>
        </div>

        <div class="card-body">
            <div class="mb-4 w-50">
                <label class="form-label fw-bold">
                    Program Year:
                </label>
                <select class="form-select form-select-md" v-model="selectedPyGuid" @change="submitForm">
                    <option v-for="programYear in programYears"
                            :key="programYear.guid"
                            :value="programYear.guid">
                        {{ programYear.start_date }} - {{ programYear.end_date }}
                    </option>
                </select>
            </div>

            <div v-if="reportData != null && reportData.publicReport != null" class="table-responsive pb-3">
                <table id="summaryReportTbl" class="table table-striped">
                    <thead>
                    <tr>
                        <th scope="col">Institution</th>
                        <th scope="col">Total Allocation</th>
                        <th scope="col">Total Claimed</th>
                        <th scope="col">Total Hold</th>
                        <th scope="col">Admin %</th>
                    </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(row, k, i) in reportData.publicReport.instList">
                            <td>&nbsp;&nbsp;{{ k }}</td>
                            <td>${{ $formatNumberWithCommas(row.total) }}</td>
                            <td>${{ $formatNumberWithCommas(row.Claimed) }}</td>
                            <td>${{ $formatNumberWithCommas(row.Hold) }}</td>
                            <td>{{ row.adminFee }}</td>
                        </tr>
                    </tbody>

                    <tfoot>
                    <tr>
                        <th scope="col">Grand Total</th>
                        <th scope="col">${{ $formatNumberWithCommas(reportData.publicReport.total) }}</th>
                        <th scope="col">${{ $formatNumberWithCommas(reportData.publicReport.Claimed) }}</th>
                        <th scope="col">${{ $formatNumberWithCommas(reportData.publicReport.Hold) }}</th>
                        <th scope="col"></th>
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
        programYears: Array,
    },
    data() {
        return {
            selectedPyGuid: this.py ? this.py.guid : '',
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
        submitForm: function () {
            let vm = this;
            let data = {
                program_year_guid: this.selectedPyGuid,
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
        if (this.selectedPyGuid) {
            this.submitForm();
        }
    }
}

</script>
