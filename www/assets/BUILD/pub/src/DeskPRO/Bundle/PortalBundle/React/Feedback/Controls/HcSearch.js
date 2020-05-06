import PropTypes from 'prop-types';
import React from 'react';
import { portalPhrases } from 'DeskPRO/Bundle/PortalBundle/PortalPhrases';

export class HcSearch extends React.Component {
  static propTypes = {
    filter:    PropTypes.object,
    forum:     PropTypes.object,
    setSearch: PropTypes.func,
  };

  static defaultProps = {
    forum: {
      plural: 'forums'
    }
  }

  constructor(props) {
    super(props);
    this.state = { q: props.filter.getQ() || '' };
  }

  setQ = (e) => {
    this.setState({ q: e.target.value });
  };

  handleSubmit = (e) => {
    this.props.setSearch(this.state.q);
    e.preventDefault();
  };

  render() {
    const { forum } = this.props;

    return (
      <form className="dp-po-community-header-search" onSubmit={this.handleSubmit}>
        <input type="text" value={this.state.q} onChange={this.setQ} placeholder={portalPhrases.get('helpcenter.community.search_forum', { forum: forum.plural })} />
        <button type="submit"><i className="dp-po-icon far fa-search" /></button>
      </form>
    );
  }
}
