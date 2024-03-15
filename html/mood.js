// Back button for mood feature
const backButton = document.querySelector('.backbutton');
backButton.addEventListener('click', () => {
window.location.href = '/index?user-interface';
});

//Mood Update Feature
const happy = document.getElementById('happy');
const fine = document.getElementById('fine');
const sad = document.getElementById('sad');

happy.addEventListener('click', () => {
    window.location.href = 'user-interface.html?mood=happy.png';
});

fine.addEventListener('click', () => {
    window.location.href = 'user-interface.html?mood=fine.png';
});

sad.addEventListener('click', () => {
    window.location.href = 'user-interface.html?mood=sad.png';
});