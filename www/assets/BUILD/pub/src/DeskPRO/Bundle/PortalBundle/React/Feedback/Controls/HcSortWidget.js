import PropTypes from 'prop-types';
import React from 'react';
import { portalPhrases } from 'DeskPRO/Bundle/PortalBundle/PortalPhrases';
import map from 'lodash/map';
import { ClickOut } from 'DeskPRO/Component/ClickOut';

export class HcSortWidget extends React.Component {

  static propTypes = {
    setSort: PropTypes.func,
  };

  constructor(props) {
    super(props);
    this.state = {
      is_shown: false,
      pos:      {
        x: 0,
        y: 0,
      }
    };
  }

  componentDidMount() {
    this.onResize();
    window.addEventListener('resize', this.onResize);
  }

  onResize = () => {
    this.setState({
      pos: {
        x: (window.width - (this.container.offsetLeft + this.container.getBoundingClientRect().width)),
        y: this.container.offsetTop,
      }
    });
  };

  onClickOut = () => {
    this.setState({ is_shown: false });
  };

  onGetRef = (el) => {
    this.container = el;
  };

  onSort = (id) => {
    this.props.setSort(id);
    this.toggle();
  };

  toggle = (ev) => {
    ev.preventDefault();
    ev.stopPropagation();
    this.setState({ is_shown: !this.state.is_shown });
  };

  render() {
    const dropdownStyle = {
      position:   'absolute',
      willChange: 'transform',
      top:        `${this.state.pos.y + 30}px`,
      right:      `${this.state.pos.x}px`,
    };

    const sorts = {
      'most-popular-desc':   portalPhrases.get('helpcenter.general.prop_popularity_desc'),
      'most-popular-asc':    portalPhrases.get('helpcenter.general.prop_popularity_asc'),
      'highest-rating-desc': portalPhrases.get('helpcenter.general.prop_rating_desc'),
      'highest-rating-asc':  portalPhrases.get('helpcenter.general.prop_rating_asc'),
      'date-desc':           portalPhrases.get('helpcenter.general.prop_date_desc'),
      'date-asc':            portalPhrases.get('helpcenter.general.prop_date_asc'),
      'most-discussed-desc': portalPhrases.get('helpcenter.general.prop_comments_desc'),
      'most-discussed-asc':  portalPhrases.get('helpcenter.general.prop_comments_asc'),
      'most-views-desc':     portalPhrases.get('helpcenter.general.prop_views_desc'),
      'most-views-asc':      portalPhrases.get('helpcenter.general.prop_views_asc')
    };

    return (
      <div className="dp-po-community-header-sort">
        <ClickOut onClickOut={this.onClickOut} context={[document]}>
          <a
            href="#toggleSort"
            className="dp-po-community-header-sort-link"
            aria-haspopup="true"
            aria-expanded={this.state.is_shown}
            aria-label={portalPhrases.get('helpcenter.general.sort')}
            title={portalPhrases.get('helpcenter.general.sort')}
            onClick={this.toggle}
            ref={this.onGetRef}
          >
            <i className="dp-po-icon fad fa-sort-alt" />
          </a>
          <div className={`dropdown-menu dropdown-menu-right ${this.state.is_shown && 'show'}`} style={dropdownStyle}>
            {map(sorts, (sort, id) =>
              <a href="#sort" key={id} onClick={(ev) => { ev.preventDefault(); ev.stopPropagation(); this.onSort(id); }} className="dropdown-item">{sort} <i className="dp-po-icon far fa-check" /></a>
            )}
          </div>
        </ClickOut>
      </div>
    );
  }
}
