<script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
<script>
$(function(){

    $('#validate_json').click(function(){
        $('#json_container').css('background-color', '#2b2a33');
        var json_data = $('#json_data').text();

        // remove the error flag if it exists (reset for revalidation)
        if(json_data.includes('¬')){
            json_data = json_data.replace('¬','');
        }

        if(!json_data){
            output('No JSON data was entered');
            return;
        }

        // Catch any syntax errors before looking for schema errors
        try{
            var parseJSON = JSON.parse(json_data);
        }catch(e){
            if((e instanceof SyntaxError)){
                $('#json_container').css('background-color', '#520e0e');
                output(e.name+"<br>"+e.message+"<br><br>Check for unclosed brackets, trailing commas and missing values.");
            }
            return;
        }

        // Format the input so highlighting makes sense
        var formatted_json = JSON.stringify(parseJSON, undefined, 4);

        $.ajax({
            type: "POST",
            url: "<?php echo get_rest_url(null, 'vvt/validate_json'); ?>",
            cache: false,
            data:{
                "json_data": json_data
            },
            success: function(data){
                var formatted_json;
                // data = JSON.parse(data);
                formatted_json = JSON.stringify(parseJSON, undefined, 4);

                if(data.error_path == false){
                    output(data.error_msg);
                }else{

                    // Edge-case of ther being an issue with the root node, handle diffrently
                    // Just highlight the first line
                    if(data.error_path == "/"){

                      var json_lines_arr = split_lines(formatted_json)
                      json_lines_arr[0] = '<div class="highlight">'+json_lines_arr[0]+'</div>';
                    }else{

                        // Use the error path to highlight the JSON error location
                        var path_targets = data.error_path.split("/");

                        // converet html entites back to what they were so matching doesn't break.
                        for(var i = 0; i < path_targets.length; i++){
                            path_targets[i] = decodeURIComponent(path_targets[i]);
                        }
                        // console.log('path_targets', path_targets);

                        // Figure out of the target were about to mark is a number, because if it is, it's an array index and marking it will destroy the object
                        // So instead mark its parent if thats not a number otherwise loop back till we find something we can mark
                        var target_offset = 1;
                        while(!isNaN(path_targets[path_targets.length-target_offset])){
                            path_targets.pop();
                            target_offset++;
                        }

                        // Mark the error ion the json obj - eval makes sense here...
                        //
                        var marked_path_targets = path_targets;
                        var target_str = path_targets.join('"]["').substring(2); // remove leading .

                        marked_path_targets[marked_path_targets.length-1] = marked_path_targets[marked_path_targets.length-1]+'¬';
                        var marked_target_str = marked_path_targets.join('"]["').substring(2); // remove leading .
                        var eval_str= 'parseJSON'+marked_target_str+'"] = parseJSON'+target_str+'"]';

                        // // Mark the error
                        // console.log("E1 ",eval_str);
                        eval(eval_str);
                        //
                        // // Delete the origional
                        // console.log("E2 ",'delete parseJSON'+target_str+'"]');
                        eval('delete parseJSON'+target_str+'"]');
                        //
                        //---------------------

                        // Need to format again since we've added the marker
                        formatted_json = JSON.stringify(parseJSON, undefined, 4);

                        var json_lines_arr = split_lines(formatted_json)

                        for(var i = 0; i < json_lines_arr.length; i++){
                            // console.log('Checking '+json_lines_arr[i]);
                            if(json_lines_arr[i].includes('¬')){
                                json_lines_arr[i] = '<div class="highlight">'+json_lines_arr[i]+'</div>';
                                break;
                            }
                        }
                    }

                    formatted_json = join_lines(json_lines_arr);
                    output(data.error_msg);
                }

                if(data.error_msg == "Valid"){
                    $('#json_container').css('background-color', '#284028');

                    // remove the error flag if it exists - were done
                    if(formatted_json.includes('¬')){
                        formatted_json = formatted_json.replace('¬','');
                    }
                }

                $('#json_data').empty();
                $('#json_data').html(formatted_json);

                // Scroll to the error
                if(data.error_msg != 'Valid' && document.getElementsByClassName("highlight")[0] != undefined){
                    var topPos = document.getElementsByClassName("highlight")[0].offsetTop;
                    document.getElementById('json_data').scrollTop = topPos - 20 - document.getElementById('json_data').offsetTop;
                    document.getElementById('json_data').scrollLeft = 0; // just incase theres a long string
                }

            } // end success handler
        });


    });

    function split_lines(str){
      return str.split(/\r?\n/);
    }
    function join_lines(arr){
      return arr.join("\n");
    }

    function output(msg){
        $("#output").empty();
        $("#output").append(msg);
    }

    // Custom format function if there's a case where JSON.stringify truncates arrays of objects that dont have a numerical index
    function format_json(json_str){

      // We need to minimise it first so formatting is consistent because we dont know how it'll be pasted in.
      json_str = json_str.replace("<br>", "");

      // Remove any previous space indents but ignore space chars inside " " strings
      json_str = json_str.replace(/([^"]+)|("[^"]+")/g, function($0, $1, $2) {
          if ($1) {
              return $1.replace(/\s/g, '');
          } else {
              return $2;
          }
      });

      json_str = json_str.replace(/[\n\r\t]/gm, "");
      // console.log('after minify');

      var indent_str = "    "; // 4 spaces
      var indent_count = 0;

      // Regex "lookaround" so the delimeter isnt removes in the split()
      //
      var lines_arr = json_str.split(/(?=[{}\[\]])|(?<=[{}\[\]])/); // commas "," intentionally missed here

      var formatted_str = '';
      for(var i = 0; i < lines_arr.length; i++){
          // console.log('Processing >> ', lines_arr[i]);

          // This goes here otherwise the closing bracket would be on the same indent level as the previously indented content
          if(lines_arr[i] == "}" || lines_arr[i] == "]"){
            indent_count--;
          }

          //formatted_str += indent_str.repeat(indent_count); // append a repeated version of the indent string based on the indent count...

          if(
            lines_arr[i] == "{" ||
            lines_arr[i] == "[" ||
            lines_arr[i].includes(",") == false
          ){
            // console.log('{ or [ :: LINE = ', lines_arr[i]);
            formatted_str += "<br>"+indent_str.repeat(indent_count)+lines_arr[i];

          }else if (lines_arr[i].includes(",") == true && lines_arr[i].indexOf(",") != lines_arr[i].lastIndexOf(",")){ // Run this if there's more than one comma

            // console.log('comma :: LINE = ', lines_arr[i]);
            // Now deal with commas
            formatted_str += indent_str.repeat(indent_count);

            var lines = lines_arr[i].split(",");
            // console.log('lines.length = ', lines.length);

            // The first element will already be indented
            formatted_str += "<br>"+indent_str.repeat(indent_count)+lines[0]+",<br>";
            // So start from 1 and finish 1 early
            for(var j = 1; j < lines.length-1; j++){
              formatted_str += indent_str.repeat(indent_count)+lines[j]+",<br>";
            }
            // The last has no comma
            formatted_str += indent_str.repeat(indent_count)+lines[lines.length-1];

          }else if(
            lines_arr[i] == "}" ||
            lines_arr[i] == "]"
          ){
            formatted_str += indent_str.repeat(indent_count);
            formatted_str += "<br>"+lines_arr[i]+"<br>";
          }else{
              // Edge case
              if(lines_arr[i].includes(",")){
                  formatted_str += "<br>"+indent_str.repeat(indent_count)+lines_arr[i];
              }else{
                  formatted_str += indent_str.repeat(indent_count)+lines_arr[i];
              }
          }

          if(lines_arr[i] == "{" || lines_arr[i] == "["){
            indent_count++;
          }
      }

      return formatted_str;
    }

});
</script>
<style>
.main_content{padding:0; margin:0; margin:15px auto; width:80%; max-width:900px; border:1px solid grey; color:white; background-color:#2b2a33;}
.content{padding:20px; font-size:10px;}
.content a{line-height:18px;}

#json_container{
  border:1px solid white; background-color:#2b2a33; color:white; font-size:12px;
}
#json_data{
  padding:0; margin:0;
  /* white-space: nowrap; */
  overflow:scroll;
  width:100%; height:500px;
}
#validate_json{
  width:250px;
  background-color: rgb(107, 107, 107);
  color:white;
  padding:5px 10px;
  border:0;
}
#validate_json:hover{cursor:pointer;}
#output_container{margin:0 auto; width:100%; height:100px; border-top:1px solid grey; border-bottom:1px solid grey;}
#output{padding:10px; width:100%; height:80px; overflow-y:scroll; font-size:12px;}

.highlight{width:100%; height:15px; background-color:rgba(255, 0, 0, 0.5)}
</style>
<div class="main_content">
    <div class="content">
        <button id="validate_json">Validate JSON syntax and schema</button><br><br>
        <div id="json_container">
          <pre id="json_data" placeholder="Enter JSON data" contenteditable></pre>
        </div>
        <br>
        Output
        <div id="output_container">
          <div id="output"></div>
        </div>
    </div>
</div>
