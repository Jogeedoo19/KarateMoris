document.addEventListener('DOMContentLoaded', function () {
    let selectedDates = [];
    let currentMembershipId = null;
    let currentMembershipName = null;
    let maxSessions = null;

    const bookedDates = window.bookedDates || []; // Array of already booked dates

    // Initialize Flatpickr
    function initializeFlatpickr() {
        const calendarInput = document.getElementById('calendar');
        flatpickr(calendarInput, {
            mode: 'multiple',
            dateFormat: 'Y-m-d',
            disable: bookedDates,
            onChange: function (selectedDatesArray) {
                handleDateSelection(selectedDatesArray);
            }
        });
    }

    // Handle Date Selection
    function handleDateSelection(dates) {
        selectedDates = dates.map(date => date.toISOString().split('T')[0]);
        updateSelectedDatesInfo();
    }

    // Update Selected Dates Info
    function updateSelectedDatesInfo() {
        const infoEl = document.getElementById('selectedPlanInfo');
        const sessionsLeft = maxSessions === 'unlimited' ?
            'Unlimited sessions available' :
            `${maxSessions - selectedDates.length} sessions remaining out of ${maxSessions}`;

        infoEl.innerHTML = `
            <strong>Selected Plan:</strong> ${currentMembershipName}<br>
            <strong>Sessions:</strong> ${sessionsLeft}<br>
            <strong>Selected Dates:</strong> ${selectedDates.length > 0 ? selectedDates.join(', ') : 'None'}
        `;

        document.getElementById('selectedDates').value = JSON.stringify(selectedDates);
    }

    // Handle Membership Selection
    document.querySelectorAll('.membership-btn').forEach(button => {
        button.addEventListener('click', function () {
            document.querySelectorAll('.pricing-item').forEach(item => {
                item.classList.remove('selected');
            });

            currentMembershipId = this.dataset.membershipId;
            currentMembershipName = this.dataset.membershipName;
            maxSessions = this.dataset.maxSessions === 'unlimited' ?
                'unlimited' : parseInt(this.dataset.maxSessions);

            this.closest('.pricing-item').classList.add('selected');
            document.getElementById('membershipId').value = currentMembershipId;
            document.getElementById('calendarSection').style.display = 'block';

            selectedDates = [];
            updateSelectedDatesInfo();

            initializeFlatpickr();
            document.getElementById('calendarSection').scrollIntoView({ behavior: 'smooth' });
        });
    });

    // Handle Form Submission
    document.getElementById('bookingForm').addEventListener('submit', function (e) {
        if (selectedDates.length === 0) {
            alert('Please select at least one training date.');
            e.preventDefault();
            return;
        }

        if (maxSessions !== 'unlimited' && selectedDates.length > maxSessions) {
            alert(`You can only select up to ${maxSessions} sessions with your current plan.`);
            e.preventDefault();
            return;
        }

        document.getElementById('selectedDates').value = JSON.stringify(selectedDates);
    });
});
