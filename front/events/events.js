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
        betsTableBody.innerHTML = '<tr><td colspan="4">Loading...</td></tr>';

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
                betsTableBody.innerHTML = '<tr><td colspan="4">Error loading data.</td></tr>';
            });
    }

    function renderBets(bets) {
        try {
            betsTableBody.innerHTML = ''; // Clear existing table body

            if (bets.length === 0) {
                betsTableBody.innerHTML = '<tr><td colspan="4">No data available</td></tr>';
                return;
            }

            const roleId = parseInt(localStorage.getItem('roleId'));

            bets.forEach(bet => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${bet.event_name}</td>
                    <td>${bet.event_date}</td>
                    <td>${bet.betting_end_date}</td>
                    <td>
                        ${roleId >= 2 ? `<button class="deleteBetButton" data-id="${bet.id}">Delete</button>` : ''}
                        <button class="placeBetButton" data-id="${bet.id}" data-outcomes='${JSON.stringify(bet.outcomes)}'>Bet</button>
                        ${roleId >= 2 ? `<button class="editBetButton" data-id="${bet.id}" data-bet='${JSON.stringify(bet)}'>Edit</button>` : ''}
                    </td>
                `;
                betsTableBody.appendChild(row);
            });

            document.querySelectorAll('.editBetButton').forEach(button => {
                button.addEventListener('click', openEditBetModal); // добавляем слушатель событий на кнопку Edit
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

    function formatDate(dateString) {
        const date = new Date(dateString);
        return date.toISOString().split('T')[0];  // Formats to 'YYYY-MM-DD'
    }
    function openEditBetModal(event) {
        const betData = JSON.parse(event.target.getAttribute('data-bet'));

        // Set up the modal fields with the event data
        document.getElementById('editBetName').value = betData.event_name;
        document.getElementById('editEventDate').value = formatDate(betData.event_date);
        document.getElementById('editBettingEndDate').value = formatDate(betData.betting_end_date);
        document.getElementById('editOption1').value = betData.outcomes[0]?.name || '';
        document.getElementById('editOption2').value = betData.outcomes[1]?.name || '';

        // Store outcome IDs in hidden fields or variables
        document.getElementById('editOption1').dataset.outcomeId = betData.outcomes[0]?.id || null;
        document.getElementById('editOption2').dataset.outcomeId = betData.outcomes[1]?.id || null;

        currentBet = betData.id;
        document.getElementById('editBetModal').style.display = 'block';

        document.getElementById('saveEditBetButton').onclick = () => saveEditBet(currentBet);
    }


    function saveEditBet(betId) {
        const updatedBet = {
            betId: betId,
            eventName: document.getElementById('editBetName').value,
            eventDate: document.getElementById('editEventDate').value,
            bettingEndDate: document.getElementById('editBettingEndDate').value,
            option1: {
                name: document.getElementById('editOption1').value,
                id: document.getElementById('editOption1').dataset.outcomeId // Include outcome ID
            },
            option2: {
                name: document.getElementById('editOption2').value,
                id: document.getElementById('editOption2').dataset.outcomeId // Include outcome ID
            },
            userId: localStorage.getItem('userId')
        };

        fetch(`http://localhost/BetsMinistry/api/?Command=EditEventCommand`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(updatedBet)
        })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    document.getElementById('editBetModal').style.display = 'none';
                    fetchBets();
                } else {
                    alert(data.message || 'Failed to edit bet.');
                }
            })
            .catch(error => {
                console.error('Error editing bet:', error);
            });
    }


    document.getElementById('cancelEditBetButton').addEventListener('click', () => {
        document.getElementById('editBetModal').style.display = 'none';
    });


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

    function getDate()
    {
        let date = new Date();
        return date.getFullYear()+"-"+date.getMonth()+"-"+date.getDate();
    }
    function saveBet() {
        const userId = localStorage.getItem('userId'); // Get userId from local storage

        const newBet = {
            eventName: document.getElementById('betName').value,
            eventDate: document.getElementById('eventDate').value || getDate(),
            bettingEndDate: document.getElementById('bettingEndDate').value || getDate(),
            option1: document.getElementById('option1').value,
            option2: document.getElementById('option2').value,
            userId: userId // Include userId in the body
        };

        fetch(`http://localhost/BetsMinistry/api/?Command=AddEventCommand`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(newBet)
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

    fetchBets(); // Load initial bets
});
