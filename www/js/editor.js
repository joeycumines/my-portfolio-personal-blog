/*
	This JavaScript file powers our post editor.
	
	Features include:
	- Automatic retrieval of remote images
	- Automatic thumbnail generation
	- Insert image at cursor button
	
*/

if (typeof location.origin === 'undefined')
location.origin = location.protocol + '//' + location.host;

//ckeditor functions
function SetContents(value) {
	// Get the editor instance that we want to interact with.
	var editor = CKEDITOR.instances.editor1;
	
	// Set editor contents (replace current contents).
	// http://docs.ckeditor.com/#!/api/CKEDITOR.editor-method-setData
	editor.setData( value );
}

function GetContents() {
	// Get the editor instance that you want to interact with.
	var editor = CKEDITOR.instances.editor1;
	
	// Get editor contents
	// http://docs.ckeditor.com/#!/api/CKEDITOR.editor-method-getData
	return editor.getData();
}



var submitIdToCall = '';
var editorIdToEdit = '';
var loading = false;
var success = false;
var content = [];

var images = [];

function isWhitespace(aChar) {
	if (aChar.charAt(0) == "\n" || aChar.charAt(0) == "\t" || aChar.charAt(0) == " ")
	return true;
	else
	return false;
} 

function triggerImages(x) {
	var xhttp = new XMLHttpRequest();
	xhttp.onreadystatechange = function() {
		if (loading) {
			if (xhttp.readyState == 4 && xhttp.status == 200) {
				try {
					var json = JSON.parse(xhttp.responseText);
					if (json['success'] == true) {
						//set uploaded 
						images[parseInt(json['imageId'])]['uploaded'] = location.origin+'/'+json['link'];
						
						x++;
						if (x < images.length) {
							triggerImages(x);
							} else {
							
							//check done
							var done = true;
							for (var y = 0; y < images.length; y++) {
								if (images[y]['uploaded'] == false) {
									done = false;
									break;
								}
							}
							if (done) {
								document.getElementById(submitIdToCall).value = 'Upload success! Saving..';
								loading = false;
								success = true;
								//insert into content, back to front.
								for (var y = images.length-1; y >=0; y--) {
									var temp = images[y]['uploaded'].split("");
									for (var z = 0; z < temp.length; z++)
									content.splice(images[y]['ind']+z, 0, temp[z]);
								}
								//set content to our id
								var newContent = '';
								for (var y = 0; y < content.length; y++) {
									newContent+=''+content[y];
								}
								document.getElementById(editorIdToEdit).value = newContent;
								SetContents(newContent);
								//submit the form
								document.getElementById(submitIdToCall).click();
								} else {
								//we fucked up somewhere
								loading = false;
								//error message here
								document.getElementById(submitIdToCall).value = 'Failed to upload images! (unknown)';
							}
						}
						} else {
						loading = false;
						//error message here
						document.getElementById(submitIdToCall).value = 'Failed to upload images! (server failure)';
					}
					} catch (err) {
					loading = false;
					//error message here
					document.getElementById(submitIdToCall).value = 'Failed to upload images! (script failure) '+err;
				}
				} else if (xhttp.readyState == 4) {
				loading = false;
				//error message here
				document.getElementById(submitIdToCall).value = 'Failed to upload images! (couldnt connect)';
			}
		}
	};
	xhttp.open("GET", location.origin+"/php/grabRemoteImage.php?imageId="+x+"&url="+images[x]['orig'], true);
	xhttp.send();
}

/**
	Run on form, pass ID of editor to retrieve, if it receives the upload text for all it continues, otherwise it fails.
*/
function stripImages(contentsId, submitButtonId) {
	//if we are not loading, and not successful, we can try again, or start
	if (loading == false && success == false) {
		//set loading to button
		var submitButton = document.getElementById(submitButtonId);
		submitButton.value = 'Uploading Images...';
		loading = true;
		submitIdToCall = submitButtonId;
		editorIdToEdit = contentsId;
		//now, find every image.
		
		//orig = original link (removed)
		//ind = string index removed from (restore from back up)
		
		/*
			Method: find all instances of img, surrounded by one opening, and one closing tag.
			- remove the src attribute if there is any
			- 
		*/
		content = document.getElementById(contentsId).value.split("");
		content = GetContents().split("");
		var foundTag = false;
		var foundImage = false;
		var foundSrc = false;
		var foundLink = false;
		var image = {};
		
		for (var x = 0; x < content.length; x++) {
			//top down.
			if (foundLink) {
				//we dont want to do it again, if the image is already localized.
				var dontWant = false;
				if (image['orig'].length <= 5) {
					dontWant = true;
				} else {
					var sHttp = ''+image['orig'][0]+image['orig'][1]+image['orig'][2]+image['orig'][3];
					var sHttps = ''+image['orig'][0]+image['orig'][1]+image['orig'][2]+image['orig'][3]+image['orig'][4];
					
					//if we aren't a relative address, are not http or https, or are to the same origin, we dont want
					if ((sHttp.toLowerCase() != 'http' && sHttps.toLowerCase() != 'https') || image['orig'].substring(0, location.origin.length).toLowerCase() == location.origin.toLowerCase()) {
						dontWant = true;
					}
				}
				
				if (dontWant) {
					//reset
					foundTag = false;
					foundImage = false;
					foundSrc = false;
					foundLink = false;
					image = {};
					} else if (content[x] == '>') {
					//looking for closing tag. No whitespace constraints.
					//add image to images and clear
					image['uploaded'] = false;
					images.push(image);
					
					foundTag = false;
					foundImage = false;
					foundSrc = false;
					foundLink = false;
					image = {};
					} else if (content[x] == '<') {
					//error, clear all 
					foundTag = false;
					foundImage = false;
					foundSrc = false;
					foundLink = false;
					image = {};
				}
				} else if (foundSrc) {
				var haveEq = false;
				var opening = '';
				while(x < content.length) {
					if (!isWhitespace(content[x])) {
						//if we are looking for eq
						if (!haveEq) {
							if (content[x] != '='){
								//reset 
								foundTag = false;
								foundImage = false;
								foundSrc = false;
								foundLink = false;
								image = {};
								break;
								} else {
								haveEq = true;
							}
							} else if (opening == '') {
							if (content[x] == '"') {
								opening = '"';
								image['orig'] = '';
								image['ind'] = -1;
								} else if (content[x] == "'") {
								opening = "'";
								image['orig'] = '';
								image['ind'] = -1;
								} else {
								//reset 
								foundTag = false;
								foundImage = false;
								foundSrc = false;
								foundLink = false;
								image = {};
								break;
							}
							} else {
							//look for closing, increment image
							if (content[x] == opening) {
								//we are at the end of the link, remove it, set x back;
								if (image['ind'] != -1) {
									//remove from content
									content.splice(image['ind'], image['orig'].length);
									//move x back
									x-=image['orig'].length;
									//set foundLink
									foundLink = true;
									} else {
									//reset 
									foundTag = false;
									foundImage = false;
									foundSrc = false;
									foundLink = false;
									image = {};
								}
								break;
								} else {
								//image link increment
								if (image['ind'] == -1) {
									image['ind'] = x;
								}
								image['orig']+= content[x];
							}
						}
					}
					x++;
				}
				} else if (foundImage) {
				//we need to get src string
				if (content[x] == '>') {
					//reset
					foundTag = false;
					foundImage = false;
					foundSrc = false;
					foundLink = false;
					image = {};
					} else if (x >= 2) {
					var temp = '' + content[x-2] + content[x-1] + content[x];
					if (temp.toLowerCase() == 'src') {
						foundSrc = true;
					}
				}
				} else if (foundTag) {
				//look for image
				if (!isWhitespace(content[x])) {
					var succ = false;
					if ((''+content[x]+content[x+1]+content[x+2]).toLowerCase() == 'img') {
						//good to go
						succ = true;
						foundImage = true;
						} else if (!succ) {
						//reset
						foundTag = false;
						foundImage = false;
						foundSrc = false;
						foundLink = false;
						image = {};
					}
				}
				} else {
				//look for tag
				if (content[x] == '<'){
					foundTag = true;
				}
			}
		}
		console.log(images);
		//get images with recursive ajax callbacks
		triggerImages(0);
		
		return false;
		} else if (loading == false && success == true) {
		//if we succeeded just return true, to save.
		return true;
	}
	return false;
}

var waitingForUpload = false;
var indivImageTimer;

//open the adding image page in (theoretically) a new tab
function addNewImage() {
	var win = window.open(location.origin+'/php/addImage.php', '_blank');
	win.focus();
}

var checkingIsImage = false;

/**
	Note only one upload at a time.
*/
function insertALocalImage(myButtonId) {
	var me = document.getElementById(myButtonId);
	if (waitingForUpload == false) {
		waitingForUpload = true;
		//start waiting for upload
		indivImageTimer = setInterval(function(){
			if (checkingIsImage) {
				//we dont do anything since we don't want to make too many connections
				} else {
				checkingIsImage = true;
				//lets start trying to get $_SESSION['UP_IMAGE']
				var xhttp = new XMLHttpRequest();
				xhttp.onreadystatechange = function() {
					if (xhttp.readyState == 4) {
						//clear the loaded flag so we can try again
						checkingIsImage = false;
					}
					if (xhttp.readyState == 4 && xhttp.status == 200 && xhttp.responseText != '') {
						//success block, we add
						CKEDITOR.instances.editor1.insertHtml('<img src="'+location.origin+'/resource/img/'+xhttp.responseText+'" alt="a image">');
						clearInterval(indivImageTimer);
						me.innerHTML = 'Insert Image at Cursor';
						waitingForUpload = false;
					}
				};
				xhttp.open("GET", location.origin+"/php/echosUP_IMAGE.php", true);
				xhttp.send();
			}
		}, 300);
		//set the spinny
		me.innerHTML = '<span class="glyphicon glyphicon-refresh glyphicon-refresh-animate"></span> Waiting For Image (click to cancel)';
		//open the upload page
		addNewImage();
		} else {
		//cancel
		clearInterval(indivImageTimer);
		me.innerHTML = 'Insert Image at Cursor';
		waitingForUpload = false;
	}
}

