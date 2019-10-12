import PropTypes from 'prop-types';
import React from 'react';

export class HcSearch extends React.Component {
  static propTypes = {
    filter:    PropTypes.object,
    setSearch: PropTypes.func,
  };

  constructor(props) {
    super(props);
    this.state = { q: '' };
  }

  setQ = (e) => {
    this.setState({ q: e.target.value });
  };

  handleSubmit = (e) => {
    this.props.setSearch(this.state.q);
    e.preventDefault();
  };

  render() {
    return (
      <form className="dp-po-community-header-search" onSubmit={this.handleSubmit}>
        <input type="text" value={this.state.q || this.props.filter.getQ()} onChange={this.setQ} placeholder="Search suggestions" />
        <button type="submit"><i className="dp-po-icon far fa-search" /></button>
      </form>
    );
  }
}
