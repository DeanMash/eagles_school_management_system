<script>

    function getLGA(state_id){
        var url = '{{ route('get_lga', [':id']) }}';
        url = url.replace(':id', state_id);
        var lga = $('#lga_id');

        $.ajax({
            dataType: 'json',
            url: url,
            success: function (resp) {
                //console.log(resp);
                lga.empty();
                $.each(resp, function (i, data) {
                    lga.append($('<option>', {
                        value: data.id,
                        text: data.name
                    }));
                });

            }
        })
    }

    function getClassSections(class_id, destination){
        var url = '{{ route('get_class_sections', [':id']) }}';
        url = url.replace(':id', class_id);
        var section = destination ? $(destination) : $('#section_id');

        $.ajax({
            dataType: 'json',
            url: url,
            success: function (resp) {
                //console.log(resp);
                section.empty();
                $.each(resp, function (i, data) {
                    section.append($('<option>', {
                        value: data.id,
                        text: data.name
                    }));
                });

            }
        })
    }

    function getClassSubjects(class_id){
        var url = '{{ route('get_class_subjects', [':id']) }}';
        url = url.replace(':id', class_id);
        var section = $('#section_id');
        var subject = $('#subject_id');

        $.ajax({
            dataType: 'json',
            url: url,
            success: function (resp) {
                console.log(resp);
                section.empty();
                subject.empty();
                $.each(resp.sections, function (i, data) {
                    section.append($('<option>', {
                        value: data.id,
                        text: data.name
                    }));
                });
                $.each(resp.subjects, function (i, data) {
                    subject.append($('<option>', {
                        value: data.id,
                        text: data.name
                    }));
                });

            }
        })
    }


    {{--Notifications--}}

    @if (session('pop_error'))
    pop({msg : '{{ session('pop_error') }}', type : 'error'});
    @endif

    @if (session('pop_warning'))
    pop({msg : '{{ session('pop_warning') }}', type : 'warning'});
    @endif

 @if (session('pop_success'))
    pop({msg : '{{ session('pop_success') }}', type : 'success', title: 'GREAT!!'});
    @endif

    @if (session('flash_info'))
      flash({msg : '{{ session('flash_info') }}', type : 'info'});
    @endif

    @if (session('flash_success'))
      @if (session('student_login_info'))
        var loginInfo = @json(session('student_login_info'));
        var msg = '{{ session('flash_success') }}' + '\\n\\nStudent Login Credentials:\\n' +
                  'Username: ' + loginInfo.username + '\\n' +
                  'Password: ' + loginInfo.password + '\\n\\n' +
                  'Please save this information securely!';
        flash({msg : msg, type : 'success'});
      @else
      flash({msg : '{{ session('flash_success') }}', type : 'success'});
      @endif
    @endif

    @if (session('flash_warning'))
      flash({msg : '{{ session('flash_warning') }}', type : 'warning'});
    @endif

     @if (session('flash_error') || session('flash_danger'))
      flash({msg : '{{ session('flash_error') ?: session('flash_danger') }}', type : 'danger'});
    @endif

    {{--End Notifications--}}

    function pop(data){
        swal({
            title: data.title ? data.title : 'Oops...',
            text: data.msg,
            icon: data.type
        });
    }

    function flash(data){
        new PNotify({
            text: data.msg,
            type: data.type,
            hide : data.type !== "danger"
        });
    }

    function confirmDelete(id) {
        swal({
            title: "Are you sure?",
            text: "Once deleted, you will not be able to recover this item!",
            icon: "warning",
            buttons: true,
            dangerMode: true
        }).then(function(willDelete){
            if (willDelete) {
             $('form#item-delete-'+id).submit();
            }
        });
    }

    function confirmReset(id) {
        swal({
            title: "Are you sure?",
            text: "This will reset this item to default state",
            icon: "warning",
            buttons: true,
            dangerMode: true
        }).then(function(willDelete){
            if (willDelete) {
             $('form#item-reset-'+id).submit();
            }
        });
    }

    $('form#ajax-reg').on('submit', function(ev){
        ev.preventDefault();
        submitForm($(this), 'store');
        $('#ajax-reg-t-0').get(0).click();
    });

    $('form.ajax-pay').on('submit', function(ev){
        ev.preventDefault();
        submitForm($(this), 'store');

//        Retrieve IDS
        var form_id = $(this).attr('id');
        var td_amt = $('td#amt-'+form_id);
        var td_amt_paid = $('td#amt_paid-'+form_id);
        var td_bal = $('td#bal-'+form_id);
        var input = $('#val-'+form_id);

        // Get Values (parseFloat for currency with decimals)
        var amt = parseFloat(td_amt.data('amount')) || 0;
        var amt_paid = parseFloat(td_amt_paid.data('amount')) || 0;
        var amt_input = parseFloat(input.val()) || 0;

//        Update Values - deduct payment from balance
        amt_paid = amt_paid + amt_input;
        var bal = Math.max(0, amt - amt_paid);

        td_bal.text(bal.toFixed(2));
        td_amt_paid.text(amt_paid.toFixed(2)).data('amount', amt_paid);
        input.attr('max', bal);
        bal < 1 ? $('#'+form_id).fadeOut('slow').remove() : '';
    });

    $('form.ajax-store').on('submit', function(ev){
        ev.preventDefault();
        submitForm($(this), 'store');
        var div = $(this).data('reload');
        div ? reloadDiv(div) : '';
    });

    $('form.ajax-update').on('submit', function(ev){
        ev.preventDefault();
        submitForm($(this));
        var div = $(this).data('reload');
        div ? reloadDiv(div) : '';
    });

    $('.download-receipt').on('click', function(ev){
        ev.preventDefault();
        $.get($(this).attr('href'));
        flash({msg : '{{ 'Download in Progress' }}', type : 'info'});
    });

    function reloadDiv(div){
        var url = window.location.href;
        url = url + ' '+ div;
        $(div).load( url );
    }

    function submitForm(form, formType){
        var btn = form.find('button[type=submit]');
        disableBtn(btn);
        var ajaxOptions = {
            url:form.attr('action'),
            type:'POST',
            cache:false,
            processData:false,
            dataType:'json',
            contentType:false,
            data:new FormData(form[0])
        };
        var req = $.ajax(ajaxOptions);
        req.done(function(resp){
            console.log('=== AJAX SUCCESS ===');
            console.log('Full Response:', JSON.stringify(resp, null, 2));
            console.log('Response type:', typeof resp);
            console.log('Response ok:', resp ? resp.ok : 'N/A');
            console.log('Response ok type:', resp ? typeof resp.ok : 'N/A');
            console.log('Response msg:', resp ? resp.msg : 'N/A');
            
            // Always re-enable button first
            enableBtn(btn);
            
            try {
                // Handle response - check if it's an object with ok/msg properties
                if (typeof resp === 'object' && resp !== null) {
                    // Check if response has ok property - be very explicit
                    var isOk = false;
                    if (resp.ok === true) isOk = true;
                    if (resp.ok === 1) isOk = true;
                    if (resp.ok === 'true') isOk = true;
                    if (resp.ok === '1') isOk = true;
                    
                    var hasMsg = resp.msg && String(resp.msg).trim() !== '';
                    
                    console.log('isOk:', isOk);
                    console.log('hasMsg:', hasMsg);
                    console.log('resp.ok value:', resp.ok);
                    console.log('resp.ok === true:', resp.ok === true);
                    
                    if (isOk && hasMsg) {
                        console.log('Showing SUCCESS message');
                        flash({msg:resp.msg, type:'success'});
                        
                        if (formType == 'store') {
                            clearForm(form);
                            // Reload page after 1.5 seconds to show new timetable in list
                            setTimeout(function() {
                                console.log('Reloading page...');
                                window.location.reload();
                            }, 1500);
                        }
                    } else if (resp.ok === false && hasMsg) {
                        console.log('Showing DANGER message (ok is false)');
                        flash({msg:resp.msg, type:'danger'});
                    } else if (hasMsg) {
                        console.log('Showing SUCCESS message (ok is undefined/null)');
                        flash({msg:resp.msg, type:'success'});
                        
                        if (formType == 'store') {
                            clearForm(form);
                            if (resp.redirect_url) {
                                window.location.href = resp.redirect_url;
                            } else {
                                setTimeout(function() { window.location.reload(); }, 1500);
                            }
                        }
                    } else {
                        console.log('No message, showing generic success');
                        flash({msg:'Operation completed successfully', type:'success'});
                        if (formType == 'store') {
                            if (resp.redirect_url) {
                                window.location.href = resp.redirect_url;
                            } else {
                                setTimeout(function() { window.location.reload(); }, 1500);
                            }
                        }
                    }
                } else {
                    console.log('Response is not an object, treating as success');
                    flash({msg: typeof resp === 'string' ? resp : 'Operation completed successfully', type:'success'});
                    if (formType == 'store' && resp && resp.redirect_url) {
                        window.location.href = resp.redirect_url;
                    } else if (formType == 'store') {
                        setTimeout(function() { window.location.reload(); }, 1500);
                    }
                }
                
                hideAjaxAlert();
                scrollTo('body');
            } catch (error) {
                console.error('Error processing success response:', error);
                console.error('Error stack:', error.stack);
                enableBtn(btn);
                flash({msg:'Operation completed but there was an error processing the response: ' + error.message, type:'warning'});
            }
            
            return resp;
        });
        req.fail(function(e){
            console.error('AJAX Error:', e);
            console.error('Status:', e.status);
            console.error('Response:', e.responseJSON);
            console.error('Response Text:', e.responseText);
            
            // Handle 200 status with invalid JSON or other issues
            if (e.status == 200) {
                console.warn('Received 200 but request failed - checking response format');
                console.log('Response Text:', e.responseText);
                console.log('Response JSON:', e.responseJSON);
                console.log('Response Headers:', e.getAllResponseHeaders());
                
                try {
                    var resp = null;
                    if (typeof e.responseJSON !== 'undefined' && e.responseJSON !== null) {
                        resp = e.responseJSON;
                        console.log('Using responseJSON');
                    } else if (e.responseText) {
                        // Try to parse response text
                        var text = e.responseText.trim();
                        // Remove any HTML/whitespace before JSON
                        var jsonStart = text.indexOf('{');
                        var jsonEnd = text.lastIndexOf('}') + 1;
                        if (jsonStart >= 0 && jsonEnd > jsonStart) {
                            text = text.substring(jsonStart, jsonEnd);
                        }
                        resp = JSON.parse(text);
                        console.log('Parsed from responseText');
                    }
                    
                    if (resp && typeof resp === 'object') {
                        console.log('Parsed response:', resp);
                        if (resp.msg) {
                            var msgType = (resp.ok === false || resp.ok === 0) ? 'danger' : 'success';
                            flash({msg: resp.msg, type: msgType});
                            
                            if (resp.ok !== false && formType == 'store') {
                                clearForm(form);
                                setTimeout(function() {
                                    window.location.reload();
                                }, 1500);
                            }
                            enableBtn(btn);
                            return;
                        }
                    }
                } catch (parseError) {
                    console.error('Failed to parse response:', parseError);
                    console.error('Response text that failed:', e.responseText);
                }
                
                // If we get here, couldn't parse response - try to show what we got
                var responsePreview = e.responseText ? e.responseText.substring(0, 200).replace(/\n/g, ' ') : 'No response text';
                console.error('Unable to parse response. Raw response:', responsePreview);
                
                // Check if it's HTML (might be an error page)
                if (responsePreview.trim().startsWith('<')) {
                    flash({msg: 'Server returned an HTML page instead of JSON. This usually means a PHP error occurred. Check server logs.', type: 'danger'});
                } else {
                    flash({msg: 'Request completed but response format was unexpected. Please try again or refresh the page.', type: 'warning'});
                }
                enableBtn(btn);
                return;
            }
            
            if (e.status == 422){
                var r = e.responseJSON || {};
                var errors = r.errors || {};
                var errorMessages = [];
                var msg = (r.msg || r.message || '').trim();
                
                var fieldLabels = { 'name': 'Timetable name', 'my_class_id': 'Class', 'title': 'Title', 'event_date': 'Event date', 'event_type': 'Event type', 'user_type': 'User type', 'gender': 'Gender', 'address': 'Address', 'nal_id': 'Nationality' };
                if (Object.keys(errors).length > 0) {
                    $.each(errors, function(field, messages) {
                        var arr = Array.isArray(messages) ? messages : (typeof messages === 'string' ? [messages] : [String(messages)]);
                        var label = fieldLabels[field] || field.replace(/_/g, ' ');
                        $.each(arr, function(_, m) {
                            if (m && String(m).trim()) errorMessages.push((label ? label + ': ' : '') + m);
                        });
                    });
                }
                if (errorMessages.length === 0 && msg) {
                    errorMessages.push(msg);
                }
                if (errorMessages.length === 0) {
                    errorMessages.push('Validation failed. Please check your input.');
                } else if (msg && msg !== 'The given data was invalid.' && errorMessages.indexOf(msg) === -1) {
                    errorMessages.unshift(msg);
                }
                displayAjaxErr(errorMessages);
                enableBtn(btn);
                return;
            } else if(e.status == 500){
                var errorMsg = e.responseJSON && e.responseJSON.msg ? e.responseJSON.msg : 
                              (e.responseJSON && e.responseJSON.message ? e.responseJSON.message : 
                              (e.status + ' ' + e.statusText + ' Please Check for Duplicate entry or Contact School Administrator/IT Personnel'));
                displayAjaxErr([errorMsg]);
            } else if(e.status == 404){
                displayAjaxErr([e.status + ' ' + e.statusText + ' - Requested Resource or Record Not Found']);
            } else if(e.status == 0 || !e.status){
                displayAjaxErr(['Network error. Please check your connection and try again.']);
            } else {
                // Other errors
                var errorMsg = e.responseJSON && e.responseJSON.msg ? e.responseJSON.msg :
                              (e.responseJSON && e.responseJSON.message ? e.responseJSON.message :
                              ('Error ' + e.status + ': ' + e.statusText));
                displayAjaxErr([errorMsg]);
            }
            enableBtn(btn);
            return e.status;
        });
        
        // Always ensure button is re-enabled after timeout (safety net)
        setTimeout(function() {
            if (btn.prop('disabled')) {
                console.warn('Button still disabled after 10 seconds - forcing enable');
                enableBtn(btn);
            }
        }, 10000);
        
        // Always ensure button is re-enabled after timeout (safety net)
        setTimeout(function() {
            if (btn.prop('disabled')) {
                console.warn('Button still disabled after 10 seconds - forcing enable');
                enableBtn(btn);
            }
        }, 10000);
    }

    function disableBtn(btn){
        if (!btn || !btn.length) return;
        // Store original HTML if not already stored
        if (!btn.data('original-html')) {
            btn.data('original-html', btn.html());
        }
        var btnText = btn.data('text') ? btn.data('text') : 'Submitting';
        btn.prop('disabled', true).html('<i class="icon-spinner mr-2 spinner"></i>' + btnText);
    }

    function enableBtn(btn){
        if (!btn || !btn.length) return;
        var btnText = btn.data('text') ? btn.data('text') : 'Submit Form';
        var originalHtml = btn.data('original-html');
        if (originalHtml) {
            btn.prop('disabled', false).html(originalHtml);
        } else {
            btn.prop('disabled', false).html(btnText + '<i class="icon-paperplane ml-2"></i>');
        }
    }

    function displayAjaxErr(errors){
        $('#ajax-alert').show().html(' <div class="alert alert-danger border-0 alert-dismissible" id="ajax-msg"><button type="button" class="close" data-dismiss="alert"><span>&times;</span></button></div>');
        $.each(errors, function(k, v){
            $('#ajax-msg').append('<span><i class="icon-arrow-right5"></i> '+ v +'</span><br/>');
        });
        scrollTo('body');
    }

    function scrollTo(el){
        $('html, body').animate({
            scrollTop:$(el).offset().top
        }, 2000);
    }

    function hideAjaxAlert(){
        $('#ajax-alert').hide();
    }

    function clearForm(form){
        form.find('.select, .select-search').val([]).select2({ placeholder: 'Select...'});
        form[0].reset();
    }



</script>