document.addEventListener('DOMContentLoaded', function () {
    const betsTableBody = document.querySelector('#betsTable tbody');
    const startDateInput = document.getElementById('startDate');
    const endDateInput = document.getElementById('endDate');
    const eventSearchInput = document.getElementById('eventSearchInput');
    const addBetButton = document.getElementById('addBetButton');
    const betModal = document.getElementById('betModal');
    const placeBetModal = document.getElementById('placeBetModal');
    const saveBetButton = document.getElementById('saveBetButton');
    const cancelButton = document.getElementById('cancelButton');
    const closeBetModalButton = document.getElementById('closeBetModalButton');

    let currentBet = null;

    function fetchBets(startDate = '', endDate = '', eventSearch = '') {
        betsTableBody.innerHTML = '<tr><td colspan="5">Loading...</td></tr>';

        fetch(`http://localhost/BetsMinistry/api/?Command=GetEventsCommand&startDate=${encodeURIComponent(startDate)}&endDate=${encodeURIComponent(endDate)}&eventSearch=${encodeURIComponent(eventSearch)}`)
            .then(response => response.json())
            .then(data => {
                // Check if the status is "success" and handle data accordingly
                if (data.status === 'success') {
                    renderBets(data.response);  // Pass the 'response' field to renderBets
                } else {
                    throw new Error('Failed to load bets: ' + data.message);
                }
            })
            .catch(error => {
                alert(error);
                betsTableBody.innerHTML = '<tr><td colspan="5">Error loading data.</td></tr>';
            });
    }

    function renderBets(bets) {
        try {
            betsTableBody.innerHTML = ''; // Clear existing table body

            if (bets.length === 0) {
                betsTableBody.innerHTML = '<tr><td colspan="6">No data available</td></tr>';
                return;
            }

            const roleId = parseInt(localStorage.getItem('roleId'));

            bets.forEach(bet => {
                const row = document.createElement('tr');

                // Convert BLOB to Base64 (assuming backend sends it as Base64 string)
                let imageUrl = bet.event_image ? `data:image/png;base64,${bet.event_image}` : '';

                row.innerHTML = `
                <td><img src="${imageUrl}" alt="Event Image" width="50" height="50"></td>
                <td>${bet.event_name}</td>
                <td>${bet.event_date}</td>
                <td>${bet.betting_end_date}</td>
                <td>
                    ${roleId >= 2 ? `<button class="deleteBetButton" data-id="${bet.id}">Delete</button>` : ''}
                    ${roleId === 1 ? `<button class="placeBetButton" data-id="${bet.id}" data-outcomes='${JSON.stringify(bet.outcomes)}'>Bet</button>` : ''}
                </td>
            `;

                betsTableBody.appendChild(row);
            });


            document.querySelectorAll('.deleteBetButton').forEach(button => {
                button.addEventListener('click', deleteEvent); // Delete bet listener
            });
            document.querySelectorAll('.placeBetButton').forEach(button => {
                button.addEventListener('click', openPlaceBetModal); // Place bet listener
            });
        } catch (error) {
            console.error('Error rendering bets:', error); // Log error to the console
            alert('An error occurred while rendering the bets. Please try again later.'); // Notify user
        }
    }



    function openPlaceBetModal(event) {
        const outcomes = event.target.getAttribute('data-outcomes');
        try {
            const parsedOutcomes = JSON.parse(outcomes);
            const selectElement = document.getElementById('betOutcome');
            selectElement.innerHTML = '';

            parsedOutcomes.forEach(outcome => {
                const option = document.createElement('option');
                option.value = outcome.id; // Set the option value to outcome ID
                option.textContent = outcome.name; // Display outcome name
                selectElement.appendChild(option);
            });

            document.getElementById('placeBetModal').style.display = 'block';

            // Set up bet placement
            const betId = event.target.getAttribute('data-id');
            document.getElementById('placeBetButton').onclick = () => placeBet(betId);
        } catch (error) {
            console.error('Error parsing outcomes:', error);
        }
    }



    function placeBet(betId) {
        const amount = document.getElementById('betAmount').value;
        const outcome = document.getElementById('betOutcome').value;
        const userId = localStorage.getItem('userId'); // Get userId from local storage

        // Prepare the body data with keys
        const bodyData = {
            betId: betId,
            amount: amount,
            outcome: outcome,
            userId: userId // Include userId in the body
        };

        fetch(`http://localhost/BetsMinistry/api/?Command=PlaceBetCommand`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(bodyData) // Send the body data with keys
        })
            .then(response => {
                // Check if the response is okay (status in the range 200-299)
                if (!response.ok) {
                    throw new Error('Network response was not ok: ' + response.statusText);
                }
                return response.json(); // Parse the JSON response
            })
            .then(data => {
                if (data.status === 'success') {
                    document.getElementById('placeBetModal').style.display = 'none';
                    alert('Bet placed successfully');
                } else if (data.status === "error") {
                    console.error(data.message || 'Failed to place bet.'); // Log error message to console
                    alert(data.message || 'Failed to place bet.');
                } else {
                    alert('Unexpected response from the server.');
                }
            })
            .catch(error => {
                // Catch and log any errors that occur during the fetch operation
                console.error('Error placing bet:', error);
            });
    }

    function openModal(title, bet = {}) {
        document.getElementById('modalTitle').innerText = title;
        document.getElementById('betName').value = bet.name || '';
        document.getElementById('eventDate').value = bet.eventDate || '';
        document.getElementById('bettingEndDate').value = bet.bettingEndDate || '';
        betModal.style.display = 'block';
        saveBetButton.onclick = () => saveBet(bet.id);
    }

    function saveBet() {
        const userId = localStorage.getItem('userId'); // Get userId from local storage

        const formData = new FormData();
        formData.append('eventName', document.getElementById('betName').value);
        formData.append('eventDate', document.getElementById('eventDate').value);
        formData.append('bettingEndDate', document.getElementById('bettingEndDate').value);
        formData.append('option1', document.getElementById('option1').value);
        formData.append('option2', document.getElementById('option2').value);
        formData.append('userId', userId); // Include userId in the body

        // Append the image file if it exists
        const eventImage = document.getElementById('eventImage').files[0];
        if (eventImage) {
            formData.append('eventImage', eventImage);
        }

        fetch(`http://localhost/BetsMinistry/api/?Command=AddEventCommand`, {
            method: 'POST',
            body: formData
        })
            .then(response => {
                // Check if the response is okay (status in the range 200-299)
                if (!response.ok) {
                    throw new Error('Network response was not ok: ' + response.statusText);
                }
                return response.json(); // Parse the JSON response
            })
            .then(data => {
                // Check the status in the response
                if (data.status === 'success') {
                    betModal.style.display = 'none';
                    fetchBets(); // Refresh bets if successful
                } else if (data.status === "error") {
                    // Log the error message to the console
                    console.error(data.message || 'Failed to save bet.');
                    alert(data.message || 'Failed to save bet.'); // Alert the user
                } else {
                    alert('Unexpected response from the server.');
                }
            })
            .catch(error => {
                // Catch and log any errors that occur during the fetch operation
                console.error('Error saving bet:', error);
            });
    }


    function deleteEvent(event) {
        const userId = localStorage.getItem('userId'); // Get userId from local storage

        const eventId = Number(event.target.getAttribute('data-id'));
        if (confirm('Are you sure you want to delete this event?')) {
            fetch(`http://localhost/BetsMinistry/api/?Command=RemoveEventCommand`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ eventId: eventId, userId: userId})
            })
                .then(response => response.json())
                .then(data => fetchBets())
                .catch(error => console.error('Error deleting event:', error));
        }
    }

    // Event listeners for filters
    eventSearchInput.addEventListener('input', function () {
        setTimeout(() => {
            const startDate = startDateInput.value;
            const endDate = endDateInput.value;
            const eventSearch = eventSearchInput.value;
            fetchBets(startDate, endDate, eventSearch.trim());
        }, 600);
    });

    startDateInput.addEventListener('change', function () {
        fetchBets(startDateInput.value, endDateInput.value, eventSearchInput.value.trim());
    });

    endDateInput.addEventListener('change', function () {
        fetchBets(startDateInput.value, endDateInput.value, eventSearchInput.value.trim());
    });

    // Add Bet Button
    addBetButton.addEventListener('click', () => {
        currentBet = null;
        openModal('Add Bet');
    });

    // Cancel button to close the modal
    cancelButton.addEventListener('click', () => {
        betModal.style.display = 'none';
    });
    closeBetModalButton.addEventListener('click', () => {
        placeBetModal.style.display = 'none';
    });

// Функция для сохранения даты в куки
    function setCookie(name, value, days) {
        let date = new Date();
        date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
        let expires = "expires=" + date.toUTCString();
        document.cookie = name + "=" + value + ";" + expires + ";path=/";
    }

// Функция для получения значения куки
    function getCookie(name) {
        let nameEQ = name + "=";
        let ca = document.cookie.split(';');
        for(let i = 0; i < ca.length; i++) {
            let c = ca[i];
            while (c.charAt(0) === ' ') c = c.substring(1, c.length);
            if (c.indexOf(nameEQ) === 0) return c.substring(nameEQ.length, c.length);
        }
        return null;
    }

// Обработчик для сохранения значений в куки и сессию
        const eventInput = document.getElementById('eventSearchInput');
        const startDate = document.getElementById('startDate');
        const endDate = document.getElementById('endDate');

        // Восстановление дат из куки при загрузке страницы
        startDate.value = getCookie('startDate') || '';
        endDate.value = getCookie('endDate') || '';

        fetch('http://localhost/BetsMinistry/api/?Command=LoadSessionEventFilterCommand', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ userId: localStorage.getItem('userId')})
        })
            .then(response => response.json())
            .then(data => {
                eventInput.value = data.response.eventTitle || '';

                fetchBets(startDate.value, endDate.value, eventInput.value.trim());

            })
            .catch(error => {
                console.error('Error loading event filter:', error);
            });


        // Событие на изменение даты, чтобы сохранять значения в куки
        startDate.addEventListener('change', function () {
            setCookie('startDate', startDate.value, 7); // Сохраняем на 7 дней
        });

        endDate.addEventListener('change', function () {
            setCookie('endDate', endDate.value, 7); // Сохраняем на 7 дней
        });

        eventInput.addEventListener('input', function () {
            const eventTitle = eventInput.value;

            fetch(`http://localhost/BetsMinistry/api/?Command=SaveSessionEventFilterCommand`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ eventTitle: eventTitle, userId: localStorage.getItem('userId')})
            })
                .then(response => response.json())
                .catch(error => console.error('Error saving session event filter:', error));
        });



    fetchBets(); // Load initial bets
});
