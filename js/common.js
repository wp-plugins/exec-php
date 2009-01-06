function ExecPhp_setMessage(heading, text)
{
	var message = '<p><strong>' + heading + '</strong> ' + text + '</p>';
	var parent = document.getElementById("execphp-message");
	try
	{
		container = document.createElement("div");
		container.className = "updated fade";
		container.innerHTML = container.innerHTML + message;
		parent.appendChild(container);
	}
	catch(e) {;}
}

/**
* Function : dump()
* Arguments: The data - array,hash(associative array),object
*    The level - OPTIONAL
* Returns  : The textual representation of the array.
* This function was inspired by the print_r function of PHP.
* This will accept some data as the argument and return a
* text that will be a more readable version of the
* array/hash/object that is given.
* Copied from: http://binnyva.blogspot.com/2005/10/dump-function-javascript-equivalent-of.html
*/
function ExecPhp_dump(arr,level) {
var dumped_text = "";
if(!level) level = 0;

//The padding given at the beginning of the line.
var level_padding = "";
for(var j=0;j<level+1;j++) level_padding += "    ";

if(typeof(arr) == 'object') { //Array/Hashes/Objects
 for(var item in arr) {
  var value = arr[item];

  if(typeof(value) == 'object') { //If it is an array,
   dumped_text += level_padding + "'" + item + "' ...\n";
   try{
    dumped_text += ExecPhp_dump(value,level+1);
   } catch(e) {
    dumped_text += "Access denied";
   }
  } else {
   dumped_text += level_padding + "'" + item + "' => \"" + value + "\"\n";
  }
 }
} else { //Stings/Chars/Numbers etc.
 dumped_text = "===>"+arr+"<===("+typeof(arr)+")";
}
return dumped_text;
}