function changeThemeSet(themeSet) {
    const colorSets = [
        ['#000080', '#008080', '#00FF00', '#FFFF00', '#00FFFF'],
        ['#0000FF', '#FFA500', '#800080', '#40E0D0', '#FFFF00'],
        ['#FFFF00', '#FFC0CB', '#FFA500', '#008000', '#A52A2A'],
        ['#0000FF', '#FFFF00', '#008080', '#808080', '#A52A2A']
    ];

    const selectedColors = colorSets[themeSet - 1]; // Arrays are 0-indexed

    document.documentElement.style.setProperty('--primary-color', selectedColors[0]);
    document.documentElement.style.setProperty('--secondary-color', selectedColors[1]);
    document.documentElement.style.setProperty('--background-color', selectedColors[2]);
    document.documentElement.style.setProperty('--accent-color', selectedColors[3]);
    document.documentElement.style.setProperty('--highlight-color', selectedColors[4]);
}