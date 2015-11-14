$(document).ready(function() {

    var page = 'index';
    var aTable = null;

    var answerForm            = $('#answerForm');
    var commentForm           = $('#commentForm');
    var newestAnswersDiv      = $('#div-newest-answers');
    var mostSearchedDiv       = $('#div-most-searched');
    var divShowAnswer         = $('#div-show-answer');
    var modalAfterSaveAnswer  = $('#modalAfterSaveAnswer');
    var modalCreateNewAnswer  = $('#modalCreateNewAnswer');
    var modalCreateNewComment = $('#modalCreateNewComment');
    var commentsDiv           = $('#comments-div');
    var answerFilesDiv        = $('#answerAttachments');
    var divShowAll            = $('#div-show-all');

    var answers = new Bloodhound({
        datumTokenizer: Bloodhound.tokenizers.obj.whitespace('title'),
        queryTokenizer: Bloodhound.tokenizers.whitespace,
        prefetch: '/api/search',
        remote: {
            url: '/api/search?query=%QUERY',
            wildcard: '%QUERY'
        },
    });

    $('#search-box').typeahead(null, {
        display: 'title',
        name: 'answer',
        source: answers,
        templates: {
            empty: [
                '<div class="empty-message">',
                    'unable to find any answers',
                '</div>'
            ].join('\n'),
            suggestion: Handlebars.compile('<div><strong><a id="answer-search-title-link" class="search-box-answer-title" href="{{uri}}">{{title}}</a></strong><br>{{description}} ...</div>')
        }
    });    

    $('#show-all').click(function(event) {
        event.preventDefault();
        page = 'show-all';        
        
        drawTable();

        hideDivs(true, true, true, false);
    });

    function drawTable() {
        var url = $('#show-all').attr('href');

        if (aTable){
            aTable.destroy();
        }

        aTable = $('#answersTable').DataTable( {
            "ajax": url,
            "columns": [
                { "data": "title" },
                { "data": "createdBy" },
                { "data": "createdAt" },
                { "data": "comments" }
            ]
        });
    }

    $(document).on('click', '#answer-search-title-link', function(event) {
        event.preventDefault();
        var url = $(this).attr('href');
        page = 'show-answer';
        clearDiv(commentsDiv);
        clearDiv(answerFilesDiv);
        if ($(this).hasClass('btn-show-saved-answer')) {
            modalAfterSaveAnswer.modal('hide');
        }
        $.ajax({
            url : url,
            type : 'GET',
            processData: false,
            contentType: 'json',
            success : function(data) {
                $('#answerId').val(data.id);
                $('#answer-title').text(data.title);
                $('#answer-created-by').text(data.created_by);
                $('#answer-created-at').text(data.created_at);
                $('#answer-description').text(data.description);
                $.each(data.comments, function(index, comment) {
                    commentsDiv.append(buildCommentBox(comment));
                });
                $.each(data.files, function(index, file) {
                    answerFilesDiv.append(buildAttachmentBox(file));
                });
                hideDivs(true, true, false, true);
            }
        });
    });

    function buildAttachmentBox(attachment) {
        return '<div class="col-md-2 attachment-icon"><a href="'+ attachment.fileURI +'" title="'+ attachment.fileName +'"><span class="glyphicon glyphicon-file" aria-hidden="true"></span></a></div>';
    }

    function buildCommentBox(comment) {
        console.log(comment);
        var attachments = '';
        if (comment.files) {
            $.each(comment.files, function(index, file) {
                 attachments += buildAttachmentBox(file);
            });
        };
        return '<div class="row"><div class="col-md-12"><hr></div><div class="col-md-12"><p id="comment-text">'+comment.text+'</p></div><div class="col-md-6">'+ attachments +'</div><div class="col-md-6"><div id="comment-created-by" class="col-md-6">'+comment.created_by+'</div><div id="comment-created-at" class="col-md-6">'+comment.created_at+'</div></div></div>';
    }

    answerForm.submit(function(event) {
        event.preventDefault();
        var formData = new FormData($(this)[0]);
        var url = $(this).attr('action');
        
        modalCreateNewAnswer.modal('hide');

        $.ajax({
            url : url,
            type : 'POST',
            data : formData,
            processData: false,
            contentType: false,
            success : function(data) {
                if (data.success) {
                    if (page == 'index') {
                        getNewestAnswers();
                    };
                    if (page == 'show-all') {
                        drawTable();
                    };
                    answerForm[0].reset();
                    $('.btn-show-saved-answer').attr('href', data.answerUri);
                    modalAfterSaveAnswer.modal('show');
                };
            }
        });
    });

    commentForm.submit(function(event) {
        event.preventDefault();
        var formData = new FormData($(this)[0]);
        var url = $(this).attr('action');
        
        modalCreateNewAnswer.modal('hide');

        $.ajax({
            url : url,
            type : 'POST',
            data : formData,
            processData: false,
            contentType: false,
            success : function(data) {
                commentForm[0].reset();
                if (page == 'show-answer') {
                    commentsDiv.empty();
                    $.each(data.comments, function(index, comment) {
                        commentsDiv.append(buildCommentBox(comment));
                    });
                    modalCreateNewComment.modal('hide');
                };
            }
        });
    });

    $('#btn-create-new-answer').click(function(event) {
        modalAfterSaveAnswer.modal('hide');
        modalCreateNewAnswer.modal('show');
    });

    function getNewestAnswers() {
        $.ajax({
            url : Routing.generate('api_get_newest_answers'),
            type : 'GET',
            contentType: 'json',
            success : function(data) {
                newestAnswersDiv.empty();
                $.each(data, function(index, val) {
                    newestAnswersDiv.append(buildAnswerBox(val));
                });
            }
        });
    }

    // function getMostSearchedAnswers() {
    //     $.ajax({
    //         url : Routing.generate('api_get_most_searched_answers'),
    //         type : 'GET',
    //         contentType: 'json',
    //         success : function(data) {
    //             $.each(data, function(index, val) {
    //                 mostSearchedDiv.append(buildAnswerBox(val));
    //             });
    //         }
    //     });
    // }

    function buildAnswerBox(answerData) {
        return '<div class="row"><div class="col-md-12"><a id="answer-search-title-link" href="' + answerData.uri + '">' + answerData.title + '</a></div><div class="col-md-12">' + answerData.description.slice(0, 100) + '</div></div>'
    }

    function clearDiv(div) {
        div.empty();
    }

    function hideDivs(newestAnswersDivOff, mostSearchedDivOff, divShowAnswerOff, divShowAllOff) {
        if (newestAnswersDivOff) {
            newestAnswersDiv.addClass('hidden');
        } else {
            newestAnswersDiv.removeClass('hidden');
        }

        if (mostSearchedDivOff) {
            mostSearchedDiv.addClass('hidden');
        } else {
            mostSearchedDiv.removeClass('hidden');
        }

        if (divShowAnswerOff) {
            divShowAnswer.addClass('hidden');
        } else {
            divShowAnswer.removeClass('hidden');
        }

        if (divShowAllOff) {
            divShowAll.addClass('hidden');
        } else {
            divShowAll.removeClass('hidden');
        }
    }

    $('#answerSave').click(function(event) {
        $('#answerForm button[type="submit"]').trigger('click');
    });

    $('#commentSave').click(function(event) {
        $('#commentForm button[type="submit"]').trigger('click');
    });

    function init() {
        if (newestAnswersDiv) {
            getNewestAnswers();
        };    

        hideDivs(false, false, true, true);
    }

    init();

    Dropzone.options.answerForm = {

        autoProcessQueue: false,
        uploadMultiple: true,
        parallelUploads: 100,
        maxFiles: 1,
        paramName: "file",

        // The setting up of the dropzone
        init: function() {
            var myDropzone = this;
            var ev;
            // First change the button to actually tell Dropzone to process the queue.
            this.element.querySelector("button[type=submit]").addEventListener("click", function(e) {
                ev = e;
                myDropzone.processQueue();
            });

            this.on("sendingmultiple", function(files, response) {
            // Gets triggered when the form is actually being sent.
            // Hide the success button or the complete form.
            });
            this.on("processing", function(file) {
            // Gets triggered when the form is actually being sent.
            // Hide the success button or the complete form.
                ev.preventDefault();
            });

            this.on("successmultiple", function(files, response) {
            // Gets triggered when the files have successfully been sent.
            // Redirect user or notify of success.
                if (page == 'index') {
                    getNewestAnswers();
                };
                if (page == 'show-all') {
                    drawTable();
                };
                modalCreateNewAnswer.modal('hide');
                answerForm[0].reset();
                modalAfterSaveAnswer.modal('show');
                this.removeAllFiles();
            });
            this.on("errormultiple", function(files, response) {
            // Gets triggered when there was an error sending the files.
            // Maybe show form again, and notify user of error
                if (page == 'index') {
                    getNewestAnswers();
                };
                if (page == 'show-all') {
                    drawTable();
                };
                modalCreateNewAnswer.modal('hide');
                answerForm[0].reset();
                modalAfterSaveAnswer.modal('show');
                this.removeAllFiles();
            });
        }

    }

    Dropzone.options.commentForm = {

        autoProcessQueue: false,
        uploadMultiple: true,
        parallelUploads: 100,
        maxFiles: 1,
        paramName: "file",

        // The setting up of the dropzone
        init: function() {
            var myDropzone = this;
            var ev;
            // First change the button to actually tell Dropzone to process the queue.
            this.element.querySelector("button[type=submit]").addEventListener("click", function(e) {
                ev = e;
                myDropzone.processQueue();
            });

            this.on("sendingmultiple", function(files, response) {
            // Gets triggered when the form is actually being sent.
            // Hide the success button or the complete form.
            });
            this.on("processing", function(file) {
            // Gets triggered when the form is actually being sent.
            // Hide the success button or the complete form.
                ev.preventDefault();
            });

            this.on("successmultiple", function(files, response) {
            // Gets triggered when the files have successfully been sent.
            // Redirect user or notify of success.
                commentsDiv.empty();
                $.each(response.comments, function(index, comment) {
                    commentsDiv.append(buildCommentBox(comment));
                });
                modalCreateNewComment.modal('hide');
                commentForm[0].reset();
                this.removeAllFiles();
            });
            this.on("errormultiple", function(files, response) {
            // Gets triggered when there was an error sending the files.
            // Maybe show form again, and notify user of error
                modalCreateNewComment.modal('hide');
                commentForm[0].reset();
                this.removeAllFiles();
            });
        }

    }

});