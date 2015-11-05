import React from 'react';
import { Popup, Header } from '../../../Form/index';

export class FilterByForm extends React.Component {

  onSubmit = event => {
    event.preventDefault();
  };

  render() {
    return (
      <Popup indicator="none">
        <Header>
          Filter
        </Header>

        <div className="sidebar-hover-content">
          <form onSubmit={this.onSubmit}>
            <div className="sidebar-hover-content-box">
              <h2>Status</h2>
              <div className="sidebar-hover-checkbox-collection inline-radio">
              </div>
            </div>
            <div className="sidebar-hover-content-box">
              <h2>Assignment</h2>
              <div className="sidebar-hover-checkbox-collection">
              </div>
              <div className="sidebar-hover-checkbox-collection">
              </div>
              <div className="sidebar-hover-checkbox-collection">
              </div>
            </div>
            <div className="sidebar-hover-content-box">
              <h2>Created</h2>
              <div className="filter-date">
                After:  &nbsp;
                <a href="#">
                  <i className="fa fa-calendar-o" /> <span className="filter-created-after">N/A</span>
                </a>
              </div>
              <div className="filter-date">
                Before:  &nbsp;
                <a href="#">
                  <i className="fa fa-calendar-o" /> <span className="filter-created-before">N/A</span>
                </a>
              </div>
            </div>
            <div className="sidebar-hover-content-box">
              <h2>Due</h2>
              <div className="filter-date">
                After:  &nbsp;
                <a href="#">
                  <i className="fa fa-calendar-o" /> <span className="filter-due-after">N/A</span>
                </a>
              </div>
              <div className="filter-date">
                Before:  &nbsp;
                <a href="#">
                  <i className="fa fa-calendar-o" /> <span className="filter-due-before">N/A</span>
                </a>
              </div>
            </div>
            <div className="sidebar-hover-content-box">
              <h2>Completed</h2>
              <div className="filter-date">
                After: &nbsp;
                <a href="#">
                  <i className="fa fa-calendar-o" /> <span className="filter-done-after">N/A</span>
                </a>
              </div>
              <div className="filter-date">
                Before: &nbsp;
                <a href="#">
                  <i className="fa fa-calendar-o" /> <span className="filter-done-before">N/A</span>
                </a>
              </div>
            </div>
            <div className="sidebar-hover-content-box">
              <h2>Projects</h2>
              <div className="sidebar-hover-checkbox-collection">

              </div>
            </div>
            <div className="sidebar-hover-content-box">
              <h2>Labels</h2>
              <div className="sidebar-hover-checkbox-collection">

              </div>
            </div>
            <div className="sidebar-hover-content-box">
              <h2>Attachments</h2>
              <div className="sidebar-hover-checkbox-collection inline-radio">

              </div>
            </div>
            <div className="sidebar-hover-content-box">
              <button type="submit" value="Apply" className="button">Apply</button> &nbsp;
              <a href="#" className="button">Clear</a>
            </div>
          </form>
        </div>
      </Popup>
    );
  }
}
