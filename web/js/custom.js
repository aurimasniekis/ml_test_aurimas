$(document).ready(function() {

    var commentEdit = function() {
        $('.comment-box').each(function() {
            var commentBox = $(this);
            var body = commentBox.find('.panel-body:not(.edit)');
            var bodyEdit = commentBox.find('.panel-body.edit');
            var editIcon = commentBox.find('a.edit');
            var cancelButton = commentBox.find('button.commnent-close');

            editIcon.click(function(e) {
                body.hide();
                bodyEdit.show();

                e.preventDefault();
                return false;
            });

            cancelButton.click(function(e) {
                body.show();
                bodyEdit.hide();

                e.preventDefault();
                return false;
            });
        });

    };

    var issueEdit = function() {
        var issueBox = $('.issue-edit-box');
        var body = issueBox.find('.panel-body:not(.edit)');
        var bodyEdit = issueBox.find('.panel-body.edit');
        var editButton = $('.edit-issue');
        var cancelButton = issueBox.find('button.commnent-close');

        editButton.click(function(e) {
            body.hide();
            bodyEdit.show();

            e.preventDefault();
            return false;
        });

        cancelButton.click(function(e) {
            body.show();
            bodyEdit.hide();

            e.preventDefault();
            return false;
        });
    };


    commentEdit();
    issueEdit();

});
