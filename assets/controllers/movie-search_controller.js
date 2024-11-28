import { Controller } from '@hotwired/stimulus';
import TomSelect from 'tom-select';

export default class extends Controller {
  static values = {
    url: String,
    minChars: Number
  }

  connect() {
    const tomSelect = new TomSelect(this.element.querySelector('input'), {
      valueField: 'id',
      labelField: 'title',
      searchField: 'title',
      minItems: 1,
      maxItems: 1,
      load: async (query, callback) => {
        if (query.length < this.minCharsValue) return callback();

        try {
          const response = await fetch(`${this.urlValue}?query=${encodeURIComponent(query)}`);
          const data = await response.json();
          callback(data);
        } catch (err) {
          callback();
        }
      },
      render: {
        option: function(item) {
          return `<div>${item.title} (${item.releaseYear})</div>`;
        },
        no_results: function() {
          return '<div class="no-results">Aucun résultat trouvé</div>';
        },
        loading: function() {
          return '<div class="no-results">Chargement...</div>';
        },
        loading_more: function() {
          return '<div class="no-results">Chargement...</div>';
        },
        no_more_results: function() {
          return '<div class="no-results">Plus de résultats</div>';
        }
      },
      onChange: (value) => {
        if (value) {
          Turbo.visit(`/movie/${value}`, {
            frame: 'modal-content'
          });
          tomSelect.clear(true);
        }
      }
    });
  }
}
