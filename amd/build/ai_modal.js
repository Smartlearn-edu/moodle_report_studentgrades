// This file is part of Moodle - https://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * AMD module for AI modal analysis.
 *
 * @module     report_studentgrades/ai_modal
 * @copyright  2025 Mohammad Nabil <mohammad@smartlearn.education>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define("report_studentgrades/ai_modal", ['jquery', 'core/modal_factory', 'core/modal_events', 'core/ajax', 'core/str'], 
    function($, ModalFactory, ModalEvents, Ajax, Str) {

    return {
        init: function() {
            var triggerBtn = $('#btn-moodle-ai-test');
            if (!triggerBtn.length) {
                return;
            }

            var userid = triggerBtn.data('userid');

            var loadingText = '';
            var generatingText = '';
            var titleText = '';
            var printText = '';
            var downloadPdfText = '';
            var successText = '';
            var unknownErrorText = '';
            var commErrorText = '';
            
            Str.get_strings([
                {key: 'talkingtocoreai', component: 'report_studentgrades'},
                {key: 'generatinganalysis', component: 'report_studentgrades'},
                {key: 'aianalysisresult', component: 'report_studentgrades'},
                {key: 'print', component: 'core'},
                {key: 'downloadpdf', component: 'report_studentgrades'},
                {key: 'communicationerror', component: 'report_studentgrades'},
                {key: 'success', component: 'report_studentgrades'},
                {key: 'unknownerror', component: 'report_studentgrades'}
            ]).done(function(strings) {
                loadingText = strings[0];
                generatingText = strings[1];
                titleText = strings[2];
                printText = strings[3];
                downloadPdfText = strings[4];
                commErrorText = strings[5];
                successText = strings[6];
                unknownErrorText = strings[7];
                
                ModalFactory.create({
                    type: ModalFactory.types.DEFAULT,
                    title: titleText,
                    body: '<div class="text-center"><i class="fa fa-spinner fa-spin fa-3x"></i><p>' + loadingText + '</p></div>',
                }, triggerBtn).done(function(modal) {
                    
                    modal.getRoot().on(ModalEvents.shown, function() {
                        // When modal shows, trigger the AJAX call
                        // Reset body to loading state in case it was opened before
                        modal.setBody('<div class="text-center" style="padding:20px;"><i class="fa fa-spinner fa-pulse fa-3x fa-fw"></i><p>' + generatingText + '</p></div>');
                        
                        Ajax.call([{
                            methodname: 'report_studentgrades_test_ai',
                            args: {
                                userid: userid
                            },
                            done: function(response) {
                                if (response && response.success) {
                                    var contentHtml = '<div id="ai-analysis-content">' + (response.message || successText) + '</div>';
                                    var controlsHtml = '<div style="margin-top:20px; text-align:right; border-top:1px solid #eee; padding-top:10px;">' +
                                        '<button class="btn btn-secondary" id="btn-print-analysis"><i class="fa fa-print"></i> ' + printText + '</button> ' +
                                        '<button class="btn btn-primary" id="btn-download-pdf"><i class="fa fa-file-pdf-o"></i> ' + downloadPdfText + '</button>' +
                                        '</div>';
                                    
                                    modal.setBody(contentHtml + controlsHtml);

                                    // Bind events
                                    setTimeout(function() {
                                        $('#btn-print-analysis').on('click', function() {
                                            var printWindow = window.open('', '', 'height=600,width=800');
                                            printWindow.document.write('<html><head><title>AI Analysis</title>');
                                            printWindow.document.write('<style>body{font-family:sans-serif; padding:20px;}</style>');
                                            printWindow.document.write('</head><body>');
                                            printWindow.document.write($('#ai-analysis-content').html());
                                            printWindow.document.write('</body></html>');
                                            printWindow.document.close();
                                            printWindow.print();
                                        });

                                        $('#btn-download-pdf').on('click', function() {
                                            var form = $('<form action="download_pdf.php" method="post" target="_blank">' +
                                                '<input type="hidden" name="action" value="downloadpdf">' +
                                                '<input type="hidden" name="userid" value="' + userid + '">' +
                                                '<textarea name="html_content" style="display:none;">' + $('#ai-analysis-content').html() + '</textarea>' +
                                                '</form>');
                                            $('body').append(form);
                                            form.submit();
                                            form.remove();
                                        });
                                    }, 500);
                                } else {
                                    var errMsg = (response && response.message) ? response.message : unknownErrorText;
                                    modal.setBody('<div class="alert alert-danger">' + errMsg + '</div>');
                                }
                            },
                            fail: function(ex) {
                                modal.setBody('<div class="alert alert-danger">' + commErrorText + ': ' + ex.message + '</div>');
                            }
                        }]);
                    });
                });
            });
        }
    };
});
