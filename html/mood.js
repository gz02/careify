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
    window.location.href = '/index?user-interface&mood=happy.png';
});

fine.addEventListener('click', () => {
    window.location.href = '/index?user-interface&mood=fine.png';
});

sad.addEventListener('click', () => {
    window.location.href = '/index?user-interface&mood=sad.png';
});

function updateMood(mood) {
    const apiUrl = //the api url goes here;

    const payload = {
        userId: 'user123', // Replace with the actual user ID fetched from the SQL table
        mood: mood
    };

    fetch(apiUrl, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(payload)
    })
    .then(response => {
        if (response.ok) {
            console.log('Mood updated successfully.');
        } else {
            console.error('Failed to update mood.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
    });
}