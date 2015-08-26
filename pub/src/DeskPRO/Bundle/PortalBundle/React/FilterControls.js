import React from "react"
import _ from "lodash"

class StatusCategory extends React.Component {
  clicked(e) {
    e.preventDefault();
    this.props.setStatusCategory(this.props.cat.id);
  }
  render() {
    return (
        <div className="cat-checkbox-title">
          <input type="checkbox" checked={this.props.isActive} onChange={this.clicked.bind(this)} />
          <a style={this.props.isActive ? { fontWeight: 'bold'} : {}} onClick={this.clicked.bind(this)}>
            {this.props.cat.title}
          </a>
        </div>
    );
  }
}

class Tab extends React.Component {
  clickTab(e) {
    e.preventDefault();
    this.props.setStatus(this.props.id);
  }
  isActiveStatusCategory(cat_id) {
    return _.includes(this.props.activeCategories, cat_id);
  }
  render() {
    let dropdownCats = this.props.available.getStatusCategoriesForStatus(this.props.id);
    let canRenderDropdown = dropdownCats.length > 0;
    return (
      <li>
        <div className={"quick-jump" + (canRenderDropdown ? "" : " no-dropdown")}>
          <a href={'/feedback/browse/' + this.props.id}
             className={this.props.active ? "active" : ""}
             onClick={this.clickTab.bind(this)}
            >
            {this.props.label}
            {this.renderActiveCats()}
            {canRenderDropdown
              ? <span><i className="fa fa-caret-down"></i></span>
              : null
            }
          </a>
          {canRenderDropdown ? this.renderDropdown(dropdownCats) : null}
        </div>
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
    if (cat_titles.length > 0) {
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

class FilterTabs extends React.Component {
  render() {
    return (
      <ul className="flat-tabs">
        {_.map(this.props.available.status, (status, status_id) => {
          return (
            <Tab
              key={status_id}
              label={status}
              id={status_id}
              active={this.props.filter.getStatus() == status_id}
              activeCategories={this.props.filter.status_categories}
              setStatus={this.props.setStatus}
              available={this.props.available}
              setStatusCategory={this.props.setStatusCategory}
            />
          );
        })}
      </ul>
    );
  }
}

class TypeSlider extends React.Component {
  click(e) {
    e.preventDefault();
    this.props.toggleType(this.props.id);
  }
  render() {
    return (
      <li>
        <div className="slider-panel">
          <a href={'/feedback/browse/type-' + this.props.id}
             className={this.props.active ? "slider" : "slider off"}
             onClick={this.click.bind(this)}>
            <span className="slider-status">{this.props.active ? "on" : "off"}</span>
            <span className="slider-icon"><i className="fa fa-check"></i></span>
          </a>
          <span className="slider-label" onClick={this.click.bind(this)}>
            {this.props.label}
          </span>
          {/**<span className="slider-options">
           <i className="fa fa-caret-down"></i>
           </span>**/}
        </div>
      </li>
    );
  }
}

class FilterTypes extends React.Component {
  render() {
    return (
      <div className="types">
        <ul className="slider-list">
          {_.map(this.props.available, (type, type_id) => {
            return (
              <TypeSlider key={type_id} label={type} id={type_id}
                          active={_.includes(this.props.selected, _.parseInt(type_id))}
                          toggleType={this.props.toggleType}/>
            );
          })}
          <li style={{float: "right", "margin-right":"20px"}}>
              <img style={{display: this.props.doSpin ? "inline" : "none", height: "30px", width: "30px"}} src={ window.DESKPRO_BASE_URL + '/web/spinner.gif' }/>
          </li>
        </ul>
      </div>
    );
  }
}

export default class FilterControls extends React.Component {
  updateFilter(filter) {
    this.props.updateFilter(filter);
  }
  setStatus(status_id) {
    let filter = this.props.filterModel;
    filter.setStatus(status_id);
    this.updateFilter(filter);
  }
  setStatusCategory(status_category_id) {
    let filter = this.props.filterModel;
    filter.toggleStatusCategory(status_category_id);
    this.updateFilter(filter);
  }
  toggleType(type_id) {
    let filter = this.props.filterModel;
    filter.toggleType(type_id);
    this.updateFilter(filter);
  }
  render() {
    return (
      <div className="feedback-filter">
        <FilterTabs
          available={this.props.available}
          filter={this.props.filterModel}
          setStatus={this.setStatus.bind(this)}
          setStatusCategory={this.setStatusCategory.bind(this)}
          />

        {/**<div className="table-meta">
          <div className="table-controls">
            <a href="#" className="column-control sort"><span>Sort</span></a>
            <a href="#" className="expand-control"><i className="fa fa-caret-right"></i></a>
          </div>
        </div>**/}

        <FilterTypes available={this.props.available.types} selected={this.props.filterModel.types} toggleType={this.toggleType.bind(this)} doSpin={this.props.doSpin} />
      </div>
    );
  }
}
