$(document).ready(function () {
  // quick search regex
  let qsRegex;
  let buttonFilter;
  const counterFrom = document.querySelector('.oib-counter-from');

  function changeCounter() {
    const bookGrid = document.querySelectorAll('.oib-grid > :not([style*="display: none"])');
    const bookCount = bookGrid.length;
    const counter = document.querySelector('.oib-counter');
    counter.innerHTML = bookCount;
    if (bookCount === 0){
      counterFrom.innerHTML = 0;
    } else {
      counterFrom.innerHTML = 1;
    }
  }

  // init Isotope
  let $grid = $(".oib-grid").isotope({
    itemSelector: ".oib-book-item",
    masonry: {
      columnWidth: 180,
      isFitWidth: true
    },
    getSortData: {
      featured_book: '.featured_book parseInt',
      number: '.number parseInt',
    },
    sortBy: [ 'featured_book', 'number' ],
    sortAscending: false,
    filter: function () {
      let $this = $(this);
      let searchResult = qsRegex ? $this.text().match(qsRegex) : true;
      let buttonResult = buttonFilter ? $this.is(buttonFilter) : true;
      return searchResult && buttonResult;
    },
  });

  $grid.isotope({ sortBy : ['featured_book', 'number'] });
  $("#oib-filters").on("click", "button", function () {
    buttonFilter = $(this).attr("data-filter");
    $grid.isotope();
    
    setTimeout(changeCounter, 550);

  });

  // use value of search field to filter
  let $quicksearch = $("#oib-quicksearch").keyup(
    debounce(function () {
      qsRegex = new RegExp($quicksearch.val(), "gi");
      $grid.isotope();
      setTimeout(changeCounter, 550);
    })
  );

  let $quicksearchclose = $("#oib-quicksearch-close").click(
    debounce(function () {
      const input = this.nextElementSibling;
      input.value = "";
      input.focus({ focusVisible: true });
      qsRegex = new RegExp($quicksearchclose.val(), "gi");
      $grid.isotope();
      setTimeout(changeCounter, 550);
    })
  );

  // change is-checked class on buttons
  $(".oib-button-group").each(function (i, buttonGroup) {
    let $buttonGroup = $(buttonGroup);
    $buttonGroup.on("click", "button", function () {
      $buttonGroup.find(".btn-danger").removeClass("btn-danger");
      $(this).addClass("btn-danger");
    });
  });

  // debounce so filtering doesn't happen every millisecond
  function debounce(fn, threshold) {
    let timeout;
    threshold = threshold || 100;
    return function debounced() {
      clearTimeout(timeout);
      let args = arguments;
      let _this = this;
      function delayed() {
        fn.apply(_this, args);
      }
      timeout = setTimeout(delayed, threshold);
    };
  }

});

