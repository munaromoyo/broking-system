<script>

function renewPolicy(policyId) {
    if(!confirm('Are you sure you want to renew this policy?')) return;

    fetch("{{ route('insurance_broking.placement_slips.renew') }}", {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}' // CRITICAL for Laravel security
        },
        body: JSON.stringify({ id: policyId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Policy Renewed! New ID: ' + data.new_id);
            // window.location.href = "/insurance-broking/placement-slips/" + data.new_id;
            window.location.href = "{{ url('insurance-broking/placement-slips') }}/" + data.new_id;
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => console.error('Error:', error));
}

</script>