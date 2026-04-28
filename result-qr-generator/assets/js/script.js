document.addEventListener('DOMContentLoaded', () => {
    const totalMarksInput = document.getElementById('total_marks');
    const obtainedMarksInput = document.getElementById('obtained_marks');
    const percentageInput = document.getElementById('percentage');

    const calculatePercentage = () => {
        if (!totalMarksInput || !obtainedMarksInput || !percentageInput) {
            return;
        }

        const totalMarks = parseFloat(totalMarksInput.value);
        const obtainedMarks = parseFloat(obtainedMarksInput.value);

        if (!Number.isFinite(totalMarks) || totalMarks <= 0 || !Number.isFinite(obtainedMarks) || obtainedMarks < 0) {
            percentageInput.value = '';
            return;
        }

        const percentage = ((obtainedMarks / totalMarks) * 100).toFixed(2);
        percentageInput.value = percentage;
    };

    [totalMarksInput, obtainedMarksInput].forEach((element) => {
        if (element) {
            element.addEventListener('input', calculatePercentage);
        }
    });

    calculatePercentage();

    document.querySelectorAll('[data-confirm-delete]').forEach((form) => {
        form.addEventListener('submit', (event) => {
            const confirmed = window.confirm('Are you sure you want to delete this result? This action cannot be undone.');

            if (!confirmed) {
                event.preventDefault();
            }
        });
    });

    document.querySelectorAll('[data-auto-dismiss]').forEach((element) => {
        setTimeout(() => {
            element.remove();
        }, 3500);
    });
});
