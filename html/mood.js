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
	saveMood("good");
});

fine.addEventListener('click', () => {
	saveMood("ok");
});

sad.addEventListener('click', () => {
	saveMood("bad");
});

function saveMood(mood) {
    const payload = {
        mood: mood
    };

    fetch("/api?save-mood", {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(payload)
    })
	.then((res) => {
		console.log(res);
		window.location.href = `/index?user-interface`;
	})
    .catch(error => {
        console.error('Error:', error);
    });
}