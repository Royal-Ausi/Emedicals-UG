// ================= Menu Navigation =================
function showSection(sectionId){
  document.querySelectorAll('main > div').forEach(sec => sec.classList.add('hidden'));
  document.getElementById(sectionId).classList.remove('hidden');
}



// ========= USER MODAL =========
function openUserModal(id = null) {
    document.getElementById('userForm').reset();
    if (id) {
        const row = document.getElementById('user-' + id);
        if (row) {
            document.getElementById('userId').value = id;
            document.getElementById('userFullname').value = row.cells[1].innerText;
            document.getElementById('userEmail').value = row.cells[2].innerText;
            document.getElementById('userType').value = row.cells[3].innerText;
            document.getElementById('userStatus').value = row.cells[4].innerText;
            document.getElementById('userModalTitle').innerText = 'Edit User';
        }
    } else {
        document.getElementById('userModalTitle').innerText = 'Add User';
    }
    document.getElementById('userModal').classList.remove('hidden');
}

function closeUserModal() {
    document.getElementById('userModal').classList.add('hidden');
}

// ========= DOCTOR MODAL =========
function openDoctorModal(id = null) {
    document.getElementById('doctorForm').reset();
    if (id) {
        const row = document.getElementById('doctor-' + id);
        if (row) {
            document.getElementById('doctorId').value = id;
            document.getElementById('doctorFullname').value = row.cells[1].innerText;
            document.getElementById('doctorSpecialization').value = row.cells[2].innerText;
            document.getElementById('doctorFee').value = row.cells[3].innerText;
            document.getElementById('doctorModalTitle').innerText = 'Edit Doctor';
        }
    } else {
        document.getElementById('doctorModalTitle').innerText = 'Add Doctor';
    }
    document.getElementById('doctorModal').classList.remove('hidden');
}

function closeDoctorModal() {
    document.getElementById('doctorModal').classList.add('hidden');
}

// ========= APPOINTMENT MODAL =========
function openAppointmentModal(id = null) {
    document.getElementById('appointmentForm').reset();
    if (id) {
        const row = document.getElementById('appointment-' + id);
        if (row) {
            document.getElementById('appointmentId').value = id;
            document.getElementById('appointmentPatient').value = row.cells[1].innerText;
            document.getElementById('appointmentDoctor').value = row.cells[2].innerText;
            // Convert date string to datetime-local format (YYYY-MM-DDTHH:MM)
            const dateStr = row.cells[3].innerText;
            // If date is in format "YYYY-MM-DD" or "YYYY-MM-DD HH:MM:SS", convert it
            let dateValue = dateStr;
            if (dateStr.includes(' ')) {
                // Format: "YYYY-MM-DD HH:MM:SS" -> "YYYY-MM-DDTHH:MM"
                dateValue = dateStr.replace(' ', 'T').substring(0, 16);
            } else if (dateStr.match(/^\d{4}-\d{2}-\d{2}$/)) {
                // Format: "YYYY-MM-DD" -> "YYYY-MM-DDTHH:MM" (default to 00:00)
                dateValue = dateStr + 'T00:00';
            }
            document.getElementById('appointmentDate').value = dateValue;
            document.getElementById('appointmentStatus').value = row.cells[4].innerText;
            document.getElementById('appointmentModalTitle').innerText = 'Edit Appointment';
        }
    } else {
        document.getElementById('appointmentModalTitle').innerText = 'Add Appointment';
    }
    document.getElementById('appointmentModal').classList.remove('hidden');
}

function closeAppointmentModal() {
    document.getElementById('appointmentModal').classList.add('hidden');
}

// ========= AVAILABILITY MODAL =========
function openAvailabilityModal(id = null) {
    document.getElementById('availabilityForm').reset();
    if (id) {
        const row = document.getElementById('availability-' + id);
        if (row) {
            document.getElementById('availabilityId').value = id;
            document.getElementById('availabilityDoctor').value = row.cells[1].innerText;
            // Format date from display format to input format (YYYY-MM-DD)
            const dateStr = row.cells[2].innerText;
            document.getElementById('availabilityDate').value = dateStr;
            // Format time from display format to input format (HH:MM)
            const startTime = row.cells[3].innerText;
            const endTime = row.cells[4].innerText;
            document.getElementById('availabilityStart').value = startTime !== 'N/A' ? startTime : '';
            document.getElementById('availabilityEnd').value = endTime !== 'N/A' ? endTime : '';
            document.getElementById('availabilityModalTitle').innerText = 'Edit Availability';
        }
    } else {
        document.getElementById('availabilityModalTitle').innerText = 'Add Availability';
    }
    document.getElementById('availabilityModal').classList.remove('hidden');
}

function closeAvailabilityModal() {
    document.getElementById('availabilityModal').classList.add('hidden');
}

// ================== AJAX Form Submission ==================

// Users
document.getElementById('userForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    const msgDiv = document.getElementById('userMessage');
    
    fetch('admin_update_user.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            const id = formData.get('id');
            if (id) {
                const row = document.getElementById('user-' + id);
                if (row) {
                    row.cells[1].innerText = formData.get('fullname');
                    row.cells[2].innerText = formData.get('email');
                    row.cells[3].innerText = formData.get('user_type');
                    row.cells[4].innerText = formData.get('status');
                }
            } else {
                location.reload();
            }
            closeUserModal();
        } else {
            msgDiv.innerHTML = '<p class="error-msg">✗ ' + (data.message || 'Failed to update') + '</p>';
        }
    })
    .catch(e => {
        msgDiv.innerHTML = '<p class="error-msg">✗ Network error: ' + e + '</p>';
    });
});

// Doctors
document.getElementById('doctorForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    const msgDiv = document.getElementById('doctorMessage');
    
    fetch('admin_update_doctor.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            const id = formData.get('id');
            if (id) {
                const row = document.getElementById('doctor-' + id);
                if (row) {
                    row.cells[1].innerText = formData.get('fullname');
                    row.cells[2].innerText = formData.get('specialization');
                    row.cells[3].innerText = formData.get('consultation_fee');
                }
            } else {
                location.reload();
            }
            closeDoctorModal();
        } else {
            msgDiv.innerHTML = '<p class="error-msg">✗ ' + (data.message || 'Failed to update') + '</p>';
        }
    })
    .catch(e => {
        msgDiv.innerHTML = '<p class="error-msg">✗ Network error: ' + e + '</p>';
    });
});

// Appointments
document.getElementById('appointmentForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    const msgDiv = document.getElementById('appointmentMessage');
    
    fetch('admin_update_appointment.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            const id = formData.get('id');
            if (id) {
                const row = document.getElementById('appointment-' + id);
                if (row) {
                    row.cells[1].innerText = formData.get('patient');
                    row.cells[2].innerText = formData.get('doctor');
                    // Format datetime-local back to display format
                    const dateValue = formData.get('appointment_date');
                    const displayDate = dateValue.replace('T', ' ').substring(0, 16);
                    row.cells[3].innerText = displayDate;
                    row.cells[4].innerText = formData.get('status');
                }
            } else {
                location.reload();
            }
            closeAppointmentModal();
        } else {
            msgDiv.innerHTML = '<p class="error-msg">✗ ' + (data.message || 'Failed to update') + '</p>';
        }
    })
    .catch(e => {
        msgDiv.innerHTML = '<p class="error-msg">✗ Network error: ' + e + '</p>';
    });
});

// Availability
document.getElementById('availabilityForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    const msgDiv = document.getElementById('availabilityMessage');
    
    fetch('admin_update_availability.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            const id = formData.get('id');
            if (id) {
                const row = document.getElementById('availability-' + id);
                if (row) {
                    row.cells[1].innerText = formData.get('doctor');
                    row.cells[2].innerText = formData.get('available_date');
                    row.cells[3].innerText = formData.get('start_time');
                    row.cells[4].innerText = formData.get('end_time');
                }
            } else {
                location.reload();
            }
            closeAvailabilityModal();
        } else {
            msgDiv.innerHTML = '<p class="error-msg">✗ ' + (data.message || 'Failed to update') + '</p>';
        }
    })
    .catch(e => {
        msgDiv.innerHTML = '<p class="error-msg">✗ Network error: ' + e + '</p>';
    });
});

// ================== Delete Functions ==================
function deleteUser(id) {
    if (confirm('Are you sure you want to delete this user? This action cannot be undone.')) {
        fetch('admin_delete_user.php?id=' + id)
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('user-' + id).remove();
                    alert('✓ User deleted successfully');
                } else {
                    alert('✗ Error: ' + (data.message || 'Failed to delete'));
                }
            })
            .catch(e => alert('✗ Network error: ' + e));
    }
}

function deleteDoctor(id) {
    if (confirm('Are you sure you want to delete this doctor?')) {
        fetch('admin_delete_doctor.php?id=' + id)
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('doctor-' + id).remove();
                    alert('✓ Doctor deleted successfully');
                } else {
                    alert('✗ Error: ' + (data.message || 'Failed to delete'));
                }
            })
            .catch(e => alert('✗ Network error: ' + e));
    }
}

function deleteAppointment(id) {
    if (confirm('Are you sure you want to delete this appointment?')) {
        fetch('admin_delete_appointment.php?id=' + id)
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('appointment-' + id).remove();
                    alert('✓ Appointment deleted successfully');
                } else {
                    alert('✗ Error: ' + (data.message || 'Failed to delete'));
                }
            })
            .catch(e => alert('✗ Network error: ' + e));
    }
}

function deleteAvailability(id) {
    if (confirm('Are you sure you want to delete this availability slot?')) {
        fetch('admin_delete_availability.php?id=' + id)
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('availability-' + id).remove();
                    alert('✓ Availability deleted successfully');
                } else {
                    alert('✗ Error: ' + (data.message || 'Failed to delete'));
                }
            })
            .catch(e => alert('✗ Network error: ' + e));
    }
}

// Close modals when clicking outside
window.addEventListener('click', function(event) {
    const modals = ['userModal', 'doctorModal', 'appointmentModal', 'availabilityModal'];
    modals.forEach(modalId => {
        const modal = document.getElementById(modalId);
        if (event.target === modal) {
            modal.classList.add('hidden');
        }
    });
});

// Close modal on Escape key
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        const modals = ['userModal', 'doctorModal', 'appointmentModal', 'availabilityModal'];
        modals.forEach(modalId => {
            document.getElementById(modalId).classList.add('hidden');
        });
    }
});
