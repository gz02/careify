var gFontSize = "Small";
var gThemeSet = 1;

function changeThemeSet(themeSet) {
	gThemeSet = themeSet;
    const colorSets = [
        ['#00FF00', '#000080', '#FFFF00', '#008080', '#00FFFF'],
        ['#40E0D0', '#800080', '#FFA500', '#0000FF', '#FFFF00'],
        ['#FFFF00', '#FFA500', '#A52A2A', '#008000', '#FFC0CB'],
        ['#808080', '#0000FF', '#FFFF00', '#A52A2A', '#008080']
    ];
	
    const selectedColors = colorSets[themeSet - 1]; // Arrays are 0-indexed

    document.documentElement.style.setProperty('--primary-color', selectedColors[0]);
    document.documentElement.style.setProperty('--secondary-color', selectedColors[1]);
    document.documentElement.style.setProperty('--background-color', selectedColors[2]);
    document.documentElement.style.setProperty('--accent-color', selectedColors[3]);
    document.documentElement.style.setProperty('--highlight-color', selectedColors[4]);
}


//Function for changing font size
function changeFontSize(size = null, name = null) {
	if (name != null) {
		gFontSize = name;
		if (name == "Medium") { size = 19; }
		else if (name == "Large") { size = 22; }
		else if (name == "Extra Large") { size = 25; }
		else { size = 16; } // 16 default
	}
	else if (size != null) {
		if (size == 19) { gFontSize = "Medium"; }
		else if (size == 22) { gFontSize = "Large"; }
		else if (size == 25) { gFontSize = "Extra Large"; }
		else { gFontSize = "Small"; } // 16 default
	}
	else {
		gFontSize = "Small";
		size = 16;
	}
	
    const root = document.documentElement;
    root.style.setProperty('--font-size', size + 'px');
}

document.addEventListener("DOMContentLoaded", function() {
	// fetch API placeholder below
	fetch('/api?theme')
	.then(ret => {
		if (!ret.ok && ret.status != 403) { console.error(ret.status, "error: requesting theme failed."); }
		return ret.json();
	})
	.then(ret => {
		if (ret.colour_theme != null) { changeThemeSet(ret.colour_theme); }
		if (ret.text_size != null) { changeFontSize(null, ret.text_size); }
	})
	.catch(error => { console.error(error); });
});
  