(function ($) {
    /**
     * Adds button/div that uses AJAX to check the given ImageMagick path.
     *
     * @param {string} url Url to the checkImageMagick action.
     * @param {string} label Label for the test button.
     */
    Annotation.Tools.checkTool = function (url, label) {
        var toolType = $('#tool_type');
        var toolCommand = $('#command');
        var toolArguments = $('#arguments');
        var toolOutputtype = $('#output_type');
        var toolOutputformat = $('#output_format');
        if (!toolCommand.length) {
            return;
        }
        var testButton = '<button type="button" id="test-button">' + label + '</button>';
        var resultDiv = '<div id="im-result" />';
        
        toolCommand.after(resultDiv);
        toolCommand.after(testButton);
        $('#test-button').click(function () {
            $.ajax({
                url: url,
                dataType: 'html',
                data: {
                    'command-to-test': toolCommand.val()
                },
                success: function (data) {
                    $('#im-result').html(data);
                }
            });
        });
    };
})(jQuery);
