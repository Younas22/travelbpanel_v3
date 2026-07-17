// form tabs active code
const buttons = document.querySelectorAll(".form-tabs-butn");
buttons.forEach(btn => {
  btn.addEventListener("click", () => {
    buttons.forEach(b => b.classList.remove("active"));
    btn.classList.add("active");
  });
});


// booking form
$(document).ready(function () {
    const fullPath = window.location.pathname.split('/');
    const baseFolder = fullPath[1]; // get the first folder
    let site;
    if (window.location.hostname === "localhost" || window.location.hostname === "127.0.0.1") {
        site = window.location.origin + "/" + baseFolder + "/";
    } else {
        site = window.location.origin + "/" + baseFolder;
    }

    function initCitySelect(selector) {
    $(selector).select2({
        placeholder: "Select City",
        allowClear: true,
        minimumInputLength: 2,
        ajax: {
            url: site + 'api/flights_airports',
            dataType: 'json',
            delay: 250,
            data: function(params) {
                return {
                    code: params.term
                };
            },
            processResults: function(data) {
                return {
                    results: data.data.map(function(item) {
                        return {
                            id: item.code,  // Using "id" field instead of "code"
                            text: `${item.city} - ${item.country} (${item.code})`
                        };
                    })
                };
            },
            cache: true
        },
        dropdownParent: $(selector).parent(),
        templateResult: function(item) {
            return item.text;
        },
        templateSelection: function(item) {
            return item.text || item.id;
        }
    }).on('select2:select', function(e) {
        // Update the underlying select element
        $(this).html('').append(
            new Option(e.params.data.text, e.params.data.id, true, true)
        );
    });
}
  // function initCitySelect(selector) {
  //   $(selector).select2({
  //     placeholder: "Select City",
  //     allowClear: true,
  //     minimumInputLength: 2, // start searching after 2 characters
  //     ajax: {
  //       url: site+'api/flights_airports',
  //       dataType: 'json',
  //       delay: 250,
  //       data: function (params) {
  //         return {
  //           code: params.term // what user typed
  //         };
  //       },
  //       processResults: function (data) {
  //         return {
  //           results: data.map(function (item) {
  //             return {
  //               id: item.code, // e.g. "LHE"
  //               text: `${item.city} - ${item.country} (${item.iataCityCode})` // e.g. "Lahore - Pakistan (LHE)"
  //             };
  //           })
  //         };
  //       },
  //       cache: true
  //     },
  //     dropdownParent: $(selector).parent()
  //   });
  // }

  // Initialize Select2 for departure and arrival
  initCitySelect('.depart-city');
  initCitySelect('.arrival-city');

  // Autofocus search field
  $(document).on('select2:open', () => {
    const searchField = document.querySelector('.select2-container--open .select2-search__field');
    if (searchField) searchField.focus();
  });

  // Swap logic
  $('.swap-btn').click(function () {
    const sectionId = $(this).data('section');
    const section = $('#search-section-' + sectionId);

    const departSelect = section.find('.depart-city');
    const arrivalSelect = section.find('.arrival-city');

    const departVal = departSelect.val();
    const arrivalVal = arrivalSelect.val();

    const departText = departSelect.find('option:selected').text();
    const arrivalText = arrivalSelect.find('option:selected').text();

    // Swap values and texts
    if (departVal || arrivalVal) {
      departSelect.empty().append(new Option(arrivalText, arrivalVal, true, true)).trigger('change');
      arrivalSelect.empty().append(new Option(departText, departVal, true, true)).trigger('change');
    }
  });
});

// $(document).ready(function () {
//   // Initialize Select2 on all city dropdowns
//   $('.city-select').each(function () {
//     $(this).select2({
//       placeholder: "Select City",
//       allowClear: true,
//       dropdownParent: $(this).parent()
//     });
//   });

//   // Autofocus on search input when Select2 opens
//   $(document).on('select2:open', () => {
//     const searchField = document.querySelector('.select2-container--open .select2-search__field');
//     if (searchField) searchField.focus();
//   });

//   // Swap departure and arrival cities
//   $('.swap-btn').click(function () {
//     const sectionId = $(this).data('section');
//     const section = $('#search-section-' + sectionId);

//     const departSelect = section.find('.depart-city');
//     const arrivalSelect = section.find('.arrival-city');

//     const departVal = departSelect.val();
//     const arrivalVal = arrivalSelect.val();

//     // Swap only if one or both values exist
//     if (departVal || arrivalVal) {
//       // Swap selected values
//       departSelect.val(arrivalVal).trigger('change');
//       arrivalSelect.val(departVal).trigger('change');
//     }
//   });
// });

// datepicker
const returnField = document.querySelector(".FDdate-field.FDreturn");
const tripRadios = document.querySelectorAll(".FDtrip-type");
const departureInput = document.querySelector(".FDdeparture-date");
const returnInput = document.querySelector(".FDreturn-date");

// Initialize variables
let departureDateValue = departureInput.value ? new Date(departureInput.value) : null;
let returnDatePicker;

// Initialize Departure Date Picker
const departureDatePicker = flatpickr(".FDdeparture-date", {
  dateFormat: "Y-m-d",
  minDate: "today",
  defaultDate: departureDateValue || null,
  allowInput: true,
  onReady: function (selectedDates, dateStr, instance) {
    if (!departureDateValue) {
      instance.input.placeholder = "Select Departure";
    }
  },
  onChange: function (selectedDates) {
    departureDateValue = selectedDates[0];

    // Update return date picker's minDate
    returnDatePicker.set('minDate', new Date(departureDateValue.getTime() + 86400000)); // +1 day

    // If round trip is selected, auto-set return date to +3 days
    if (document.querySelector('.FDtrip-type[data-type="return"]:checked')) {
      const returnDate = new Date(departureDateValue);
      returnDate.setDate(returnDate.getDate() + 3);
      returnDatePicker.setDate(returnDate);
    }
  }
});

// Initialize Return Date Picker
returnDatePicker = flatpickr(".FDreturn-date", {
  dateFormat: "Y-m-d",
  minDate: departureDateValue ? new Date(departureDateValue.getTime() + 86400000) : "today",
  allowInput: true,
  defaultDate: null,
  onReady: function (selectedDates, dateStr, instance) {
    if (!returnInput.value) {
      instance.input.placeholder = "Select Return";
    }
  }
});

// Show/hide return field and handle auto-setting return date
tripRadios.forEach(radio => {
  radio.addEventListener("change", function () {
    const selectedType = this.getAttribute("data-type");

    if (selectedType === "return") {
      returnField.classList.remove("FDhidden");

      // Auto-set return date to +3 days if departure exists
      if (departureDateValue) {
        const returnDate = new Date(departureDateValue);
        returnDate.setDate(returnDate.getDate() + 3);
        returnDatePicker.setDate(returnDate);
      }
    } else {
      returnField.classList.add("FDhidden");
      returnDatePicker.clear();
    }
  });
});




// Initialize FSbox functionality for a given box
function initializeFSbox(box) {
  const passengerTrigger = box.querySelector(".FSpassenger-trigger");
  const passengerPopup = box.querySelector(".FSpopup-passenger");
  const addBtn = box.querySelector(".FSadd-btn");
  const totalSeat = box.querySelector(".FStotal-seat");

  const classTrigger = box.querySelector(".FSclass-trigger");
  const classPopup = box.querySelector(".FSclass-popup");
  const selectedClass = box.querySelector(".FSselected-class");

  // Initial counts per box
  const counts = {
    adult: window.adult,
    child: window.child,
    infant: window.infant
  };

  // Toggle passenger popup
  passengerTrigger.addEventListener("click", (e) => {
    e.stopPropagation();
    // Close other popups on the page
    document.querySelectorAll('.FSpopup-passenger.show').forEach(p => {
      if (p !== passengerPopup) p.classList.remove('show');
    });
    document.querySelectorAll('.FSclass-popup.show').forEach(p => {
      if (p !== classPopup) p.classList.remove('show');
    });
    passengerPopup.classList.toggle("show");
    classPopup.classList.remove("show");
  });

  // Toggle class popup
  classTrigger.addEventListener("click", (e) => {
    e.stopPropagation();
    // Close other popups on the page
    document.querySelectorAll('.FSpopup-passenger.show').forEach(p => {
      if (p !== passengerPopup) p.classList.remove('show');
    });
    document.querySelectorAll('.FSclass-popup.show').forEach(p => {
      if (p !== classPopup) p.classList.remove('show');
    });
    classPopup.classList.toggle("show");
    passengerPopup.classList.remove("show");
  });

  // Plus buttons
  // Plus buttons with max seat validation and error message toggle
box.querySelectorAll(".FSplus").forEach(btn => {
  btn.addEventListener("click", (e) => {
    e.stopPropagation();
    const type = btn.dataset.type;
    const errorMsg = box.querySelector(".FSerror-msg");
    const currentTotal = counts.adult + counts.child + counts.infant;

    if (currentTotal < 9) {
      counts[type]++;
      box.querySelector(`.FScount-value[data-type="${type}"]`).textContent = counts[type];

      // Hide message when seat count is valid
      if (errorMsg) {
        errorMsg.style.display = "none";
        errorMsg.textContent = "";
      }
    } else {
      // Show message when seat count exceeds limit
      if (errorMsg) {
        errorMsg.textContent = "Maximum limit is 9 seats.";
        errorMsg.style.display = "block";
      }
    }
  });
});


  // box.querySelectorAll(".FSplus").forEach(btn => {
  //   btn.addEventListener("click", (e) => {
  //     e.stopPropagation();
  //     const type = btn.dataset.type;
  //     counts[type]++;
  //     box.querySelector(`.FScount-value[data-type="${type}"]`).textContent = counts[type];
  //   });
  // });

  // Minus buttons
  box.querySelectorAll(".FSminus").forEach(btn => {
    btn.addEventListener("click", (e) => {
      e.stopPropagation();
      const type = btn.dataset.type;
      if (counts[type] > 0 && !(type === "adult" && counts[type] === 1)) {
        counts[type]--;
        box.querySelector(`.FScount-value[data-type="${type}"]`).textContent = counts[type];
      }
    });
  });

  // Add button updates total seats
  addBtn.addEventListener("click", (e) => {
    e.stopPropagation();
    const total = counts.adult + counts.child + counts.infant;
    totalSeat.textContent = `${total} Seat${total > 1 ? 's' : ''}`;
    passengerPopup.classList.remove("show");
  });

  // Class selection
  box.querySelectorAll(".FSclass-option").forEach(option => {
    option.addEventListener("click", (e) => {
      e.stopPropagation();
      const selected = option.dataset.class;
      const containerWidth = selectedClass.offsetWidth;
      const canvas = document.createElement("canvas");
      const ctx = canvas.getContext("2d");
      ctx.font = "700 1.1rem Arial";

      let text = selected;
      while (ctx.measureText(text).width > containerWidth && text.length > 0) {
        text = text.slice(0, -1);
      }
      selectedClass.textContent = ctx.measureText(selected).width > containerWidth ? text.slice(0, -3) + "..." : selected;
      selectedClass.title = selected;
      classPopup.classList.remove("show");
    });
  });
}

// Initialize FSbox for existing boxes on page load
document.querySelectorAll('.FSbox').forEach(box => {
  initializeFSbox(box);
});

// Hide popups when clicking outside
document.addEventListener("click", () => {
  document.querySelectorAll(".FSpopup-passenger.show").forEach(popup => popup.classList.remove("show"));
  document.querySelectorAll(".FSclass-popup.show").forEach(popup => popup.classList.remove("show"));
});

// multi city create new flight row
const multiCityRowsContainer = document.getElementById("multi-city-rows");
const multiCityControls = document.getElementById("multi-city-controls");
const addFlightBtn = document.getElementById("add-flight-btn");
const clearFlightsBtn = document.getElementById("clear-flights-btn");

// Store the original row for cloning
const originalRow = document.querySelector(".main-flight-row").cloneNode(true);

// When "Multi-city" radio is selected
document.querySelectorAll(".FDtrip-type").forEach(radio => {
  radio.addEventListener("change", function () {
    const selectedType = this.getAttribute("data-type");

    if (selectedType === "multiCity") {
      multiCityControls.style.display = "block";
      multiCityRowsContainer.innerHTML = "";
      addNewFlightRow();
    } else {
      multiCityControls.style.display = "none";
      multiCityRowsContainer.innerHTML = "";
    }
  });
});

// Add a new flight row
function addNewFlightRow() {
  const clone = originalRow.cloneNode(true);

  // Remove IDs from cloned elements
  clone.querySelectorAll("[id]").forEach(el => el.removeAttribute("id"));

  // Clear input fields
  clone.querySelectorAll("input").forEach(input => input.value = "");

  // Find or create the search container in the row
  let searchContainer = clone.querySelector(".search-container");
  if (!searchContainer) {
    searchContainer = document.createElement("div");
    searchContainer.className = "search-container";
    searchContainer.innerHTML = `
     <!-- Depart City -->
              <div class="city-item">
                <div class="icon-circle">
                  <img src="{{ url('public/assets/images/departure.png') }}" width="16px" height="16px" />
                </div>
                <div class="w-100 border-start ps-2 position-relative">
                  <div class="city-label ms-1">Departure city</div>
                  <!-- Common city options -->
                  <select class="city-select depart-city">
                    <option></option>
                    <option value="Bali - IND">Bali - IND</option>
                    <option value="Jakarta - IND">Jakarta - IND</option>
                    <option value="Tokyo - JPN">Tokyo - JPN</option>
                    <option value="New York - USA">New York - USA</option>
                    <option value="London - UK">London - UK</option>
                    <option value="Sydney - AUS">Sydney - AUS</option>
                    <option value="Dubai - UAE">Dubai - UAE</option>
                    <option value="Abu Dhabi - UAE">Abu Dhabi - UAE</option>
                    <option value="Paris - FRA">Paris - FRA</option>
                    <option value="Toronto - CAN">Toronto - CAN</option>
                    <option value="Singapore - SGP">Singapore - SGP</option>
                    <option value="Mumbai - IND">Mumbai - IND</option>
                  </select>

                </div>
              </div>

              <!-- Swap Button -->
              <button class="btn swap-btn" data-section="1">
                <img src="{{ url('public/assets/images/switch.png') }}" width="16px" height="16px" />
              </button>

              <!-- Arrival City -->
              <div class="city-item">
                <div class="icon-circle ms-3">
                  <img src="{{ url('public/assets/images/arrival.png') }}" width="16px" height="16px" />
                </div>
                <div class="w-100 border-start ps-2 position-relative">
                  <div class="city-label ms-1">Arrival city</div>
                  <select class="city-select arrival-city">
                    <option></option>
                    <option value="Bali - IND">Bali - IND</option>
                    <option value="Jakarta - IND">Jakarta - IND</option>
                    <option value="Tokyo - JPN">Tokyo - JPN</option>
                    <option value="New York - USA">New York - USA</option>
                    <option value="London - UK">London - UK</option>
                    <option value="Sydney - AUS">Sydney - AUS</option>
                    <option value="Dubai - UAE">Dubai - UAE</option>
                    <option value="Abu Dhabi - UAE">Abu Dhabi - UAE</option>
                    <option value="Paris - FRA">Paris - FRA</option>
                    <option value="Toronto - CAN">Toronto - CAN</option>
                    <option value="Singapore - SGP">Singapore - SGP</option>
                    <option value="Mumbai - IND">Mumbai - IND</option>
                  </select>

                </div>
              </div>
    `;
    clone.prepend(searchContainer);
  }

  // Append to new row container
  multiCityRowsContainer.appendChild(clone);

  // Initialize Select2 for the new row
  initializeSelect2(clone);

  // Re-initialize datepickers
  flatpickr(clone.querySelector(".FDdeparture-date"), {
    dateFormat: "Y-m-d",
    defaultDate: "today"
  });

  flatpickr(clone.querySelector(".FDreturn-date"), {
    dateFormat: "Y-m-d"
  });

  // Re-initialize FSbox for the new row
  const newFSbox = clone.querySelector('.FSbox');
  if (newFSbox) {
    initializeFSbox(newFSbox);
  }
}

// Initialize Select2 and swap functionality for a container
function initializeSelect2(container) {
  // Initialize Select2 dropdowns
  $(container).find('.city-select').each(function () {
    $(this).select2({
      placeholder: "Select City",
      allowClear: true,
      dropdownParent: $(this).parent()
    });
  });

  // Set up swap functionality for this container
  $(container).find('.swap-btn').off('click').on('click', function () {
    const section = $(this).closest('.search-container');

    // Get current values and data
    let departSelect = section.find('.depart-city');
    let arrivalSelect = section.find('.arrival-city');

    let departVal = departSelect.val();
    let arrivalVal = arrivalSelect.val();

    // Only swap if at least one city is selected
    if (departVal || arrivalVal) {
      // Get full option data
      let departData = departVal ? departSelect.find('option[value="' + departVal + '"]') : null;
      let arrivalData = arrivalVal ? arrivalSelect.find('option[value="' + arrivalVal + '"]') : null;

      // Swap the values
      if (departVal) {
        arrivalSelect.empty();
        if (arrivalVal) {
          arrivalSelect.append(new Option(departData.text(), departData.val(), true, true));
        } else {
          arrivalSelect.append(new Option(departData.text(), departData.val(), false, false));
        }
      }

      if (arrivalVal) {
        departSelect.empty();
        if (departVal) {
          departSelect.append(new Option(arrivalData.text(), arrivalData.val(), true, true));
        } else {
          departSelect.append(new Option(arrivalData.text(), arrivalData.val(), false, false));
        }
      }

      // Trigger changes
      departSelect.val(arrivalVal).trigger('change');
      arrivalSelect.val(departVal).trigger('change');

      // Force refresh Select2 displays
      departSelect.select2('destroy').select2({
        placeholder: "Select City",
        allowClear: true,
        dropdownParent: departSelect.parent()
      });

      arrivalSelect.select2('destroy').select2({
        placeholder: "Select City",
        allowClear: true,
        dropdownParent: arrivalSelect.parent()
      });
    }
  });
}

// Auto-focus search field when dropdown opens (global handler)
$(document).on('select2:open', () => {
  let searchField = document.querySelector('.select2-container--open .select2-search__field');
  if (searchField) searchField.focus();
});

// Handle "Add More Flights" click
addFlightBtn.addEventListener("click", () => {
  addNewFlightRow();
});

// Handle "Clear All" click
clearFlightsBtn.addEventListener("click", () => {
  multiCityRowsContainer.innerHTML = "";
  addNewFlightRow(); // Re-add one row for usability
});

// Initialize the first row if needed
if (document.querySelector('.FDtrip-type[data-type="multiCity"]:checked')) {
  multiCityControls.style.display = "block";
  addNewFlightRow();
}



// home page slider
const carousel = document.getElementById('cardCarousel');
const prevBtn = document.getElementById('prevBtn');
const nextBtn = document.getElementById('nextBtn');

let currentSlide = 0;

function getVisibleCards() {
  return window.innerWidth >= 992 ? 3 : window.innerWidth >= 768 ? 2 : 1;
}

function updateCarousel() {
  const cardWidth = carousel.querySelector('.card-item').offsetWidth;
  carousel.style.transform = `translateX(-${currentSlide * cardWidth}px)`;
}

nextBtn.addEventListener('click', () => {
  const cardCount = carousel.querySelectorAll('.card-item').length;
  const visibleCards = getVisibleCards();

  if (currentSlide < cardCount - visibleCards) {
    currentSlide++;
  } else {
    // Loop to start
    currentSlide = 0;
  }
  updateCarousel();
});

prevBtn.addEventListener('click', () => {
  const cardCount = carousel.querySelectorAll('.card-item').length;
  const visibleCards = getVisibleCards();

  if (currentSlide > 0) {
    currentSlide--;
  } else {
    // Loop to last full view
    currentSlide = cardCount - visibleCards;
  }
  updateCarousel();
});

window.addEventListener('resize', () => {
  updateCarousel();
});



// FLight list page
