import React from 'react';
import { StatusCategory } from './StatusCategory';
import { TouchFocusWidget } from 'DeskPRO/Bundle/PortalBundle/PageWidget/TouchFocusWidget';
import _ from 'lodash';

export class Tab extends React.Component {

  clickTab(e) {
    e.preventDefault();
    this.props.setStatus(this.props.id);
  }

  isActiveStatusCategory(cat_id) {
    return _.includes(this.props.activeCategories, cat_id);
  }

  componentDidMount() {
    if (this.refs.quickJump) {
      const $el = $(this.refs.quickJump);

      const widget = new TouchFocusWidget($el);
      widget.render();
    }
  }

  renderActive() {
    let dropdownCats = this.props.available.getStatusCategoriesForStatus(this.props.id);
    let canRenderDropdown = dropdownCats.length > 0;
    return (
      <div className={"quick-jump" + (canRenderDropdown ? "" : " no-dropdown")} ref="quickJump">
        <span className={this.props.active ? "link active" : "link"}>
          {this.props.label}
          {this.renderActiveCats()}
          {canRenderDropdown ? <span><i className="fa fa-caret-down"></i></span> : null}
        </span>
        {canRenderDropdown ? this.renderDropdown(dropdownCats) : null}
      </div>
    );
  }

  renderInactive() {
    return (
      <div className={"quick-jump no-dropdown"} ref="quickJump">
        <a href={'/feedback/browse/' + this.props.id}
           className={this.props.active ? "active" : ""}
           onClick={this.clickTab.bind(this)}
           onTouchStart={this.clickTab.bind(this)}
        >
          {this.props.label}
        </a>
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
  renderActiveCats() {
    let cat_titles = _.map(this.props.activeCategories, (active_status_category) => {
      let cat = this.props.available.getStatusCategoryById(this.props.id, active_status_category);
      if (typeof cat !== 'undefined') {
        return cat.title;
      }
    });

    cat_titles = _.filter(cat_titles, (title) => {
      return typeof title !== 'undefined';
    });

    let avail = this.props.available.getStatusCategoriesForStatus(this.props.id);

    if (cat_titles.length > 0 && cat_titles.length !== avail.length) {
      return (<small> ({cat_titles.length}/{avail.length})</small>);
    }
  }

  renderDropdown(dropdownCats) {
    return (
      <div className="dropdown-content">
        <ul>
          {_.map(dropdownCats, (cat) => {
            return (
              <li key={cat.id}>
                <StatusCategory
                  cat={cat}
                  isActive={this.isActiveStatusCategory(cat.id)}
                  setStatusCategory={this.props.setStatusCategory}
                  />
              </li>
            );
          })}
        </ul>
      </div>
    );
  }
}
