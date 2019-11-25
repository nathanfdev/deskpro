import React from 'react';
import PropTypes from 'prop-types';
import { Seq } from 'immutable';
import { FormattedMessage } from 'react-intl';
import { Input, Button, Loader } from '@deskpro/react-components';
import { faSearch } from '@fortawesome/free-solid-svg-icons';
import { api } from 'DeskPRO/Bundle/AppBundle/DAL';
import debounce from 'lodash/debounce';

export default class UnsplashImageBrowser extends React.Component {
  static propTypes = {
    closeModal:  PropTypes.func,
    selectImage: PropTypes.func,
  };

  static onFocus() {
    window.DeskPRO_Window.keyboardShortcuts.isPaused = true;
  }

  static onBlur() {
    window.DeskPRO_Window.keyboardShortcuts.isPaused = false;
  }

  constructor(props) {
    super(props);
    this.state = {
      query:         '',
      page:          1,
      imagesLoading: true,
      unsplash:      Seq(),
    };
    this.debouncedSearch = debounce(this.searchImages, 500);
  }

  componentDidMount() {
    this.randomImages();
  }
  onFilterChange = (value) => {
    this.setState({
      imagesLoading: true,
      query:         value,
      page:          1
    });
    this.debouncedSearch();
  };

  loadMore = () => {
    const { query, page } = this.state;
    if (query) {
      this.setState({
        page: page + 1
      });
      this.searchImages();
    } else {
      this.randomImages();
    }
  };

  randomImages = (clear = false) => {
    const { unsplash } = this.state;
    return api.sendGet('DP_API/apps/unsplash/random?count=12').success((response) => {
      if (clear) {
        this.setState({
          unsplash: Seq(response)
        });
      } else {
        this.setState({
          unsplash: unsplash.concat(response)
        });
      }
    }).then(() => {
      this.setState({
        imagesLoading: false
      });
    });
  };

  searchImages = () => {
    const { query, page, unsplash } = this.state;
    if (query === '') {
      return this.randomImages(true);
    }
    return api.sendGet(`DP_API/apps/unsplash/search?per_page=12&page=${page}&query=${query}`).success((response) => {
      if (page === 1) {
        this.setState({
          unsplash: Seq(response)
        });
      } else {
        this.setState({
          unsplash: unsplash.concat(response)
        });
      }
    }).then(() => {
      this.setState({
        imagesLoading: false
      });
    });
  };

  selectImage = (image) => {
    this.props.selectImage(image);
    this.props.closeModal();
  };

  renderImages = () => {
    const { unsplash } = this.state;

    if (this.state.imagesLoading) {
      return (
        <div className="images">
          <div className="loading">
            <Loader size="medium" />
          </div>
        </div>
      );
    }
    return (
      <div className="images">
        {unsplash.map(image => (
          <div
            className="image"
            key={image.id}
            onClick={() => this.selectImage(image)}
            style={{
              backgroundImage:    `url(${image.urls.thumb})`,
              backgroundPosition: '0 0',
              backgroundSize:     'cover'
            }}
          >
            <span className="attribution">
              <a
                href={`${image.user.links.html}?utm_source=deskpro&utm_medium=referral`}
                target="_blank"
                rel="noopener noreferrer"
              >
                {image.user.name}
              </a> by <a
                href="https://unsplash.com/"
                target="_blank"
                rel="noopener noreferrer"
              >
                Unsplash
              </a>
            </span>
          </div>
          )
        )}
        <div className="load-more" >
          <Button onClick={this.loadMore}><FormattedMessage id="agent.tickets.load_more" /></Button>
        </div>
      </div>
    );
  };

  render() {
    const { query } = this.state;
    return (
      <div className="unsplash_image_browser">
        <Input
          value={query}
          type="search"
          icon={faSearch}
          className="dp-input--large"
          onChange={this.onFilterChange}
          onFocus={UnsplashImageBrowser.onFocus}
          onBlur={UnsplashImageBrowser.onBlur}
        />
        {this.renderImages()}
      </div>
    );
  }
}
