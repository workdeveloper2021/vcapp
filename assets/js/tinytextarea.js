$(document).ready(function () {
        if($("#tinytextarea").length > 0){
          
            tinymce.init({
                selector: "textarea#tinytextarea",
                theme: "modern",
                height:100,
                plugins: [
                    "save directionality emoticons template paste textcolor"
                ],
                toolbar: "insertfile undo redo | styleselect | bold italic |  emoticons",
                
                emoticons_database_url: '/emojis.js',
                max_chars : "250",
                charLimit : 20, // this is a default value which can get modified later
                
                setup: function (ed) {
                  var allowedKeys = [8, 37, 38, 39, 40, 46]; // backspace, delete and cursor keys
                  ed.on('keydown', function (e) {
                      if (allowedKeys.indexOf(e.keyCode) != -1) return true;
                      if (tinymce_getContentLength() + 1 >= this.settings.max_chars) {
                          e.preventDefault();
                          e.stopPropagation();
                          return false;
                      }
                      return true;
                  });
                  ed.on('keyup', function (e) {
                      tinymce_updateCharCounter(this, tinymce_getContentLength());
                  });
              },
              init_instance_callback: function () { // initialize counter div
                  $('#' + this.id).prev().append('<div class="char_count" style="text-align:right"></div>');
                  tinymce_updateCharCounter(this, tinymce_getContentLength());
              },
              /*paste_preprocess: function (plugin, args) {
                  var editor = tinymce.get(tinymce.activeEditor.id);
                  var len = editor.contentDocument.body.innerText.length;
                  var text = $(args.content).text();
                  if (len + text.length > editor.settings.max_chars) {
                      swal('Pasting this exceeds the maximum allowed number of ' + editor.settings.max_chars + ' characters.');
                      args.content = '';
                  } else {
                      tinymce_updateCharCounter(editor, len + text.length);
                  }
              }*/



            }); 


            function tinymce_updateCharCounter(el, len) {
                $('#' + el.id).prev().find('.char_count').text(len + '/' + el.settings.max_chars);
            }

            function tinymce_getContentLength() {
                return tinymce.get(tinymce.activeEditor.id).contentDocument.body.innerText.length;
            }           
        }

    function CountCharacters() {
        var body = tinymce.get("tinytextarea").getBody();
        var content = tinymce.trim(body.innerText || body.textContent);
        return content.length;
    };
    function ValidateCharacterLength() {
        var max = 2;
        var count = CountCharacters();
        $(this).val($(this).val().substring(0,2));
        if (count > max) {
            alert("Maximum " + max + " characters allowed.")
            return false;
        }
        return;
    }

    $( "form#toot_add" ).submit(function( event ) {  
       var count = CountCharacters();
        if(count > 250){
          swal('You can not enter more than 250 character, please enter under 250 character text');
          $('#add_loader_form_submit').hide();  
          event.preventDefault(); 
          return false; 
        }else{
           $('#add_loader_form_submit').show();
          return true;
        }
        
      });  
   });
