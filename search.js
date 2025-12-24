// Search auto-suggestions
const searchInput = document.getElementById('searchInput');
const searchSuggestions = document.getElementById('searchSuggestions');
let searchTimeout;

searchInput.addEventListener('input', function() {
  clearTimeout(searchTimeout);
  const query = this.value.trim();
  
  if (query.length < 2) {
    searchSuggestions.style.display = 'none';
    return;
  }
  
  searchTimeout = setTimeout(() => {
    fetchSearchSuggestions(query);
  }, 300);
});

searchInput.addEventListener('focus', function() {
  const query = this.value.trim();
  if (query.length >= 2 && searchSuggestions.innerHTML.trim() !== '') {
    searchSuggestions.style.display = 'block';
  }
});

document.addEventListener('click', function(e) {
  if (!searchInput.contains(e.target) && !searchSuggestions.contains(e.target)) {
    searchSuggestions.style.display = 'none';
  }
});

function fetchSearchSuggestions(query) {
  fetch(`search_suggestions.php?q=${encodeURIComponent(query)}`)
    .then(response => response.json())
    .then(data => {
      displaySuggestions(data);
    })
    .catch(error => {
      console.error('Error fetching suggestions:', error);
    });
}

function displaySuggestions(suggestions) {
  if (suggestions.length === 0) {
    searchSuggestions.innerHTML = '<div class="search-suggestion-item">No results found</div>';
    searchSuggestions.style.display = 'block';
    return;
  }
  
  let html = '';
  suggestions.forEach(item => {
    html += `
      <div class="search-suggestion-item" onclick="selectSuggestion('${item.name}')">
        ${item.image ? `<img src="${item.image}" alt="${item.name}">` : ''}
        <div class="search-suggestion-info">
          <div class="search-suggestion-name">${item.name}</div>
          <div class="search-suggestion-price">LE ${item.price}</div>
        </div>
      </div>
    `;
  });
  
  searchSuggestions.innerHTML = html;
  searchSuggestions.style.display = 'block';
}

function selectSuggestion(name) {
  searchInput.value = name;
  searchSuggestions.style.display = 'none';
  searchInput.focus();
}