function changeThemeSet(themeSet) {
    const colorSets = [
        ['#000080', '#008080', '#00FF00', '#FFFF00', '#00FFFF'],
        ['#0000FF', '#FFFF00', '#FFA500', '#40E0D0', '#800080'],
        ['#FFFF00', '#FFA500', '#FFC0CB', '#008000', '#A52A2A'],
        ['#0000FF', '#808080', '#FFFF00', '#008080', '#A52A2A']
    ];

    const selectedColors = colorSets[themeSet - 1]; // Arrays are 0-indexed

    document.documentElement.style.setProperty('--primary-color', selectedColors[0]);
    document.documentElement.style.setProperty('--secondary-color', selectedColors[1]);
    document.documentElement.style.setProperty('--background-color', selectedColors[2]);
    document.documentElement.style.setProperty('--accent-color', selectedColors[3]);
    document.documentElement.style.setProperty('--highlight-color', selectedColors[4]);
}


//Function for changing font size
function changeFontSize(size) {
    const root = document.documentElement;
    root.style.setProperty('--font-size', size + 'px');
  }
  