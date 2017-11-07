import PropTypes from 'prop-types';
import React from 'react';

export class SearchForm extends React.Component {

  static propTypes = {
    handleType:   PropTypes.func.isRequired,
    handleSearch: PropTypes.func.isRequired,
    handleClear:  PropTypes.func.isRequired,
    searching:    PropTypes.any.isRequired
  };

  handleClear = (event) => {
    event.preventDefault();
    this.refs.searchBox.value = '';
    this.props.handleClear();
  };

  render() {
    let clearInputClassName = 'clear-input';
    if (this.props.searching) {
      clearInputClassName += ' visible';
    } else {
      clearInputClassName += ' invisible';
    }

    return (
      <div className="active-chat-search">
        <form onSubmit={this.props.handleSearch}>
          <input type="text" ref="searchBox" placeholder="Search chat history" id="active-chat-search-input" onChange={this.props.handleType} />
            <a href="#" onClick={this.handleClear} className={clearInputClassName} id="active-chat-search-clear">
              <i className="fa fa-times"></i>
            </a>
          <input type="submit" value="&#xf002;" />
        </form>
      </div>
    );
  }
}
