<!-- User Modal -->
<div id="userModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
  <div class="bg-white p-6 rounded shadow-lg w-96">
    <h3 id="userModalTitle" class="text-xl font-bold mb-4">Add User</h3>
    <form id="userForm">
      <input type="hidden" name="id" id="user-id">
      <label class="block mb-2">Full Name
        <input type="text" name="fullname" id="user-fullname" class="w-full border p-2 rounded">
      </label>
      <label class="block mb-2">Email
        <input type="email" name="email" id="user-email" class="w-full border p-2 rounded">
      </label>
      <label class="block mb-2">Type
        <select name="user_type" id="user-type" class="w-full border p-2 rounded">
          <option value="patient">Patient</option>
          <option value="doctor">Doctor</option>
          <option value="admin">Admin</option>
        </select>
      </label>
      <label class="block mb-2">Status
        <select name="status" id="user-status" class="w-full border p-2 rounded">
          <option value="active">Active</option>
          <option value="pending">Pending</option>
          <option value="inactive">Inactive</option>
        </select>
      </label>
      <div class="flex justify-end mt-4 space-x-2">
        <button type="button" onclick="closeUserModal()" class="px-3 py-1 bg-gray-300 rounded hover:bg-gray-400">Cancel</button>
        <button type="submit" class="px-3 py-1 bg-blue-500 text-white rounded hover:bg-blue-600">Save</button>
      </div>
    </form>
  </div>
</div>

<!-- Doctor Modal -->
<div id="doctorModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
  <div class="bg-white p-6 rounded shadow-lg w-96">
    <h3 id="doctorModalTitle" class="text-xl font-bold mb-4">Add Doctor</h3>
    <form id="doctorForm">
      <input type="hidden" name="id" id="doctor-id">
      <label class="block mb-2">Full Name
        <input type="text" name="fullname" id="doctor-fullname" class="w-full border p-2 rounded">
      </label>
      <label class="block mb-2">Specialization
        <input type="text" name="specialization" id="doctor-specialization" class="w-full border p-2 rounded">
      </label>
      <label class="block mb-2">Consultation Fee
        <input type="number" name="consultation_fee" id="doctor-fee" class="w-full border p-2 rounded">
      </label>
      <div class="flex justify-end mt-4 space-x-2">
        <button type="button" onclick="closeDoctorModal()" class="px-3 py-1 bg-gray-300 rounded hover:bg-gray-400">Cancel</button>
        <button type="submit" class="px-3 py-1 bg-blue-500 text-white rounded hover:bg-blue-600">Save</button>
      </div>
    </form>
  </div>
</div>

<!-- Appointment Modal -->
<div id="appointmentModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
  <div class="bg-white p-6 rounded shadow-lg w-96">
    <h3 id="appointmentModalTitle" class="text-xl font-bold mb-4">Add Appointment</h3>
    <form id="appointmentForm">
      <input type="hidden" name="id" id="appointment-id">
      <label class="block mb-2">Patient
        <select name="patient_id" id="appointment-patient" class="w-full border p-2 rounded">
          <?php foreach ($users as $u) if($u['user_type']=='patient') echo "<option value='{$u['id']}'>{$u['fullname']}</option>"; ?>
        </select>
      </label>
      <label class="block mb-2">Doctor
        <select name="doctor_id" id="appointment-doctor" class="w-full border p-2 rounded">
          <?php foreach ($doctors as $d) echo "<option value='{$d['id']}'>{$d['fullname']}</option>"; ?>
        </select>
      </label>
      <label class="block mb-2">Date
        <input type="datetime-local" name="appointment_date" id="appointment-date" class="w-full border p-2 rounded">
      </label>
      <label class="block mb-2">Status
        <select name="status" id="appointment-status" class="w-full border p-2 rounded">
          <option value="pending">Pending</option>
          <option value="confirmed">Confirmed</option>
          <option value="cancelled">Cancelled</option>
        </select>
      </label>
      <div class="flex justify-end mt-4 space-x-2">
        <button type="button" onclick="closeAppointmentModal()" class="px-3 py-1 bg-gray-300 rounded hover:bg-gray-400">Cancel</button>
        <button type="submit" class="px-3 py-1 bg-blue-500 text-white rounded hover:bg-blue-600">Save</button>
      </div>
    </form>
  </div>
</div>

<!-- Availability Modal -->
<div id="availabilityModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
  <div class="bg-white p-6 rounded shadow-lg w-96">
    <h3 id="availabilityModalTitle" class="text-xl font-bold mb-4">Add Availability</h3>
    <form id="availabilityForm">
      <input type="hidden" name="id" id="availability-id">
      <label class="block mb-2">Doctor
        <select name="doctor_id" id="availability-doctor" class="w-full border p-2 rounded">
          <?php foreach ($doctors as $d) echo "<option value='{$d['id']}'>{$d['fullname']}</option>"; ?>
        </select>
      </label>
      <label class="block mb-2">Date
        <input type="date" name="available_date" id="availability-date" class="w-full border p-2 rounded">
      </label>
      <label class="block mb-2">Start Time
        <input type="time" name="start_time" id="availability-start" class="w-full border p-2 rounded">
      </label>
      <label class="block mb-2">End Time
        <input type="time" name="end_time" id="availability-end" class="w-full border p-2 rounded">
      </label>
      <div class="flex justify-end mt-4 space-x-2">
        <button type="button" onclick="closeAvailabilityModal()" class="px-3 py-1 bg-gray-300 rounded hover:bg-gray-400">Cancel</button>
        <button type="submit" class="px-3 py-1 bg-blue-500 text-white rounded hover:bg-blue-600">Save</button>
      </div>
    </form>
  </div>
</div>
