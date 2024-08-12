$(document).ready(function () {
  $("#search-input").on("keyup", function () {
    let query = $(this).val();

    if (query.length >= 3) {
      // Only start search after 3 or more characters
      $.ajax({
        url: "index.php?route=product/category.ajaxSearch",
        type: "post",
        data: { query: query },
        dataType: "json",
        success: function (json) {
          // Clear previous results
          $("#ajax-search-products-results").html("");
          $("#ajax-search-categories-results").html("");

          // Add products
          if (json.products.length) {
                var productHtml = "<h5>Products</h5>";
                productHtml += `<div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4">`;
                $.each(json.products, function (index, product) {
                    productHtml += product;
                });
                productHtml += `</div>`;
                $("#ajax-search-products-results").append(productHtml);
                addbutton = `<a class="primaryButton" href="index.php?route=product/search&search=`+query+`">View all</a>`;
                $("#ajax-search-products-results").append(addbutton);
            }

          // Add categories
          if (json.categories.length) {
            let categoryHtml = "<h5>Categories</h5>";
            categoryHtml += `<div class="row categories gy-5">`;
            $.each(json.categories, function (index, category) {
                categoryHtml += category;
            });
            categoryHtml += `</div>`;
            $("#ajax-search-categories-results").append(categoryHtml);
            $(".search-result-item").on('click', function () { // Assuming search-result-item is a class added to clickable items
              saveSearch(query);
            });
          }
          if(json.categories.length == 0 && json.products.length == 0){
            let noHtml = "<h5>No Results Found</h5>";
            noHtml += `<img src="image/search_notfound.svg"/>`;
            $("#ajax-search-categories-results").append(noHtml);
          }
        },
      });
    }
  });
  $("#search-input-mobile").on("keyup", function () {
    let query = $(this).val();

    if (query.length >= 3) {
      // Only start search after 3 or more characters
      $.ajax({
        url: "index.php?route=product/category.ajaxSearch",
        type: "post",
        data: { query: query },
        dataType: "json",
        success: function (json) {
          // Clear previous results
          $("#ajax-search-products-results-mobile").html("");
          $("#ajax-search-categories-results-mobile").html("");

          // Add products
          if (json.products.length) {
                var productHtml = "<h5>Products</h5>";
                productHtml += `<div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4">`;
                $.each(json.products, function (index, product) {
                    productHtml += product;
                });
                productHtml += `</div>`;
                $("#ajax-search-products-results-mobile").append(productHtml);
                addbutton = `<a class="primaryButton" href="index.php?route=product/search&search=`+query+`">View all</a>`;
                $("#ajax-search-products-results-mobile").append(addbutton);
            }

          // Add categories
          if (json.categories.length) {
            let categoryHtml = "<h5>Categories</h5>";
            categoryHtml += `<div class="row categories gy-5">`;
            $.each(json.categories, function (index, category) {
                categoryHtml += category;
            });
            categoryHtml += `</div>`;
            $("#ajax-search-categories-results-mobile").append(categoryHtml);
            $(".search-result-item").on('click', function () { // Assuming search-result-item is a class added to clickable items
              saveSearch(query);
            });
          }
          if(json.categories.length == 0 && json.products.length == 0){
            let noHtml = "<h5>No Results Found</h5>";
            noHtml += `<img src="image/search_notfound.svg"/>`;
            $("#ajax-search-categories-results-mobile").append(noHtml);
          }
        },
      });
    }
  });
});

function saveSearch(query) {
  // Retrieve recent searches from the cookie
  var recentSearches = getCookie("recentSearches");
  if (recentSearches) {
    recentSearches = JSON.parse(recentSearches);
  } else {
    recentSearches = [];
  }

  // Check if the query is already present in recent searches
  if (!recentSearches.includes(query)) {
    // Add the query to the recent searches if not already present
    recentSearches.push(query);

    // Limit to 5 recent searches
    if (recentSearches.length > 5) {
      recentSearches.shift();
    }

    // Convert the string to a Date object
    var expires = new Date();
    expires.setDate(expires.getDate() + 30);

    // Save the updated recent searches back to the cookie
    setCookie("recentSearches", JSON.stringify(recentSearches), expires.toUTCString());
  }
}


function getCookie(name) {
  var cookieValue = document.cookie.match('(^|; )' + name + '=([^;]*)(;|$)');
  if (cookieValue) {
    return cookieValue[2];
  } else {
    return null;
  }
}

function setCookie(name, value, expires) {
  var cookie = name + "=" + value + "; expires=" + expires + "; path=/";
  document.cookie = cookie;
}

function showRecentSearches() {
  let searches = [];
  let container = document.querySelector('.recent-searches');
  container.innerHTML = ''; // Clear existing searches

  // Get recent searches from cookie
  searches = JSON.parse(getCookie("recentSearches") || "[]");

  // Check if there are any recent searches
  if (searches.length > 0) {
    // Show the h5 tag
    const h5Tag = document.getElementById('recent_searches');
    h5Tag.style.display = 'block';

    // Add the recent searches
    searches.forEach(search => {
      let searchTag = document.createElement('div');
      searchTag.className = 'search-tag';

      let link = document.createElement('div');

      let img = document.createElement('img');
      img.src = 'image/timeIcon.svg';

      let span = document.createElement('span');
      span.textContent = search;

      link.appendChild(img);
      link.appendChild(span);
      searchTag.appendChild(link);
      container.appendChild(searchTag);

      // Add an onclick listener to the search tag
      searchTag.addEventListener('click', function() {
        // Get the search value from the input field
        const searchInput = document.getElementById('search-input');
        const searchValue = searchInput.value;

        // Update the search value
        searchInput.value = search;

        // Trigger a keyup event on the input field
        searchInput.dispatchEvent(new KeyboardEvent("keyup"));
      });
    });
  } else {
    // Hide the h5 tag
    const h5Tag = document.getElementById('recent_searches');
    h5Tag.style.display = 'none';
  }
}

function showRecentSearchesMobile() {
  let searches = [];
  let container = document.querySelector('.recent-searches-mobile');
  container.innerHTML = ''; // Clear existing searches

  // Get recent searches from cookie
  searches = JSON.parse(getCookie("recentSearches") || "[]");

  // Check if there are any recent searches
  if (searches.length > 0) {
    // Show the h5 tag
    const h5Tag = document.getElementById('recent_searches_mobile');
    h5Tag.style.display = 'block';

    // Add the recent searches
    searches.forEach(search => {
      let searchTag = document.createElement('div');
      searchTag.className = 'search-tag';

      let link = document.createElement('div');

      let img = document.createElement('img');
      img.src = 'image/timeIcon.svg';

      let span = document.createElement('span');
      span.textContent = search;

      link.appendChild(img);
      link.appendChild(span);
      searchTag.appendChild(link);
      container.appendChild(searchTag);

      // Add an onclick listener to the search tag
      searchTag.addEventListener('click', function() {
        // Get the search value from the input field
        const searchInput = document.getElementById('search-input');
        const searchValue = searchInput.value;

        // Update the search value
        searchInput.value = search;

        // Trigger a keyup event on the input field
        searchInput.dispatchEvent(new KeyboardEvent("keyup"));
      });
    });
  } else {
    // Hide the h5 tag
    const h5Tag = document.getElementById('recent_searches_mobile');
    h5Tag.style.display = 'none';
  }
}


document.addEventListener('DOMContentLoaded', showRecentSearches);
document.addEventListener('DOMContentLoaded', showRecentSearchesMobile);

