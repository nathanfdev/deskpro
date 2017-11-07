import PropTypes from 'prop-types';
import React from 'react';
import filter from 'lodash/filter';
import includes from 'lodash/includes';
import map from 'lodash/map';
import $ from 'jquery';
import { TouchFocusWidget } from 'DeskPRO/Bundle/PortalBundle/PageWidget/TouchFocusWidget';
import { StatusCategory } from './StatusCategory';

export class Tab extends React.Component {
  static propTypes = {
    id:                PropTypes.string,
    available:         PropTypes.object,
    active:            PropTypes.bool,
    activeCategories:  PropTypes.array,
    label:             PropTypes.string,
    types:             PropTypes.string,
    setStatus:         PropTypes.func,
    setStatusCategory: PropTypes.func,
  };

  constructor(props) {
    super(props);

    this.clickTab = this.clickTab.bind(this);
  }

  componentDidMount() {
    if (this.quickJump) {
      const $el = $(this.quickJump);

      const widget = new TouchFocusWidget($el);
      widget.render();
    }
  }

  isActiveStatusCategory(catId) {
    return includes(this.props.activeCategories, catId);
  }

  clickTab(e) {
    e.preventDefault();
    this.props.setStatus(this.props.id);
  }

  renderActive() {
    const dropdownCats = this.props.available.getStatusCategoriesForStatus(this.props.id);
    const canRenderDropdown = dropdownCats.length > 0;
    return (
      <div className={`quick-jump${canRenderDropdown ? '' : ' no-dropdown'}`} ref={(c) => { this.quickJump = c; }}>
        <span className={this.props.active ? 'link active' : 'link'}>
          {this.props.label}
          {this.renderActiveCats()}
          {canRenderDropdown ? <span><i className="fa fa-caret-down" /></span> : null}
        </span>
        {canRenderDropdown ? this.renderDropdown(dropdownCats) : null}
      </div>
    );
  }

  renderInactive() {
    return (
      <div className={'quick-jump no-dropdown'} ref={(c) => { this.quickJump = c; }}>
        <a
          href={`/feedback/browse/${this.props.id}/${this.props.types}`}
          className={this.props.active ? 'active' : ''}
          onClick={this.clickTab}
          onTouchStart={this.clickTab}
        >
          {this.props.label}
        </a>
      </div>
    );
  }

  renderActiveCats() {
    let catTitles = map(this.props.activeCategories, (activeStatusCategory) => {
      const cat = this.props.available.getStatusCategoryById(this.props.id, activeStatusCategory);
      if (typeof cat !== 'undefined') {
        return cat.title;
      }
      return null;
    });

    catTitles = filter(catTitles, title => typeof title !== 'undefined');

    const avail = this.props.available.getStatusCategoriesForStatus(this.props.id);

    if (catTitles.length > 0 && catTitles.length !== avail.length) {
      return (<small> ({catTitles.length}/{avail.length})</small>);
    }
    return null;
  }

  renderDropdown(dropdownCats) {
    return (
      <div className="dropdown-content no-touch-focus">
        <ul>
          {map(dropdownCats, cat =>
            <li key={cat.id}>
              <StatusCategory
                cat={cat}
                isActive={this.isActiveStatusCategory(cat.id)}
                setStatusCategory={this.props.setStatusCategory}
              />
            </li>
          )}
        </ul>
      </div>
    );
  }

  render() {
    return (
      <li>
        {this.props.active ? this.renderActive() : this.renderInactive()}
      </li>
    );
  }
}
