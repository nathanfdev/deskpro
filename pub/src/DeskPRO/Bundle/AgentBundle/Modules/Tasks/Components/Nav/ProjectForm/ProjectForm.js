import React from 'react';
import { Scrollable } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Scrollable';

export class ProjectForm extends React.Component {

  render() {
    return (
      <div className="sidebar-hover">
        <div className="dpmw--popup-main">
          <div className="dpmw--popup-header">
            <i className="fa fa-tags"/> Project - Create New
          </div>

          <form>
            <div className="dpw--popup-content">

              <div className="dpw--popup-content-line">
                <div className="dpmw--popup-content-full">
                  <inpit name="projectId" type="hidden" />
                  <h2 className="dpw--popup-item-section-title">Title</h2>
                  <div className="dpw--popup-form-container">
                    <input name="title" type="text" placeholder="Title" />
                  </div>
                </div>
              </div>

              <div className="dpw--popup-content-line">
                <div className="dpw--popup-content-left">
                  <div className="dpw-quick-filter">
                    <div className="dpw-quick-filter-container">
                      <div className="dpw-quick-filter-icon"><i className="fa fa-filter" /></div>
                      <input type="text" placeholder="Quick Filter" />
                      <span className="dpw-quick-filter-clear-link"><i className="fa fa-times-circle"></i></span>
                    </div>
                  </div>
                </div>

                <div className="dpmw--popup-content-right">
                  <div className="dpw-popup-content-item">
                    <div className="dpw-popup-content-item-show-only-selected">
                      <a href="#" className="checkbox-link'">
                        <span>Show only Selected</span>
                        <span className="dpw--checkbox-boxy"><i className="fa fa-check" /></span>
                      </a>
                    </div>
                  </div>
                  <div className="dpw-popup-content-item">
                    <div className="dpw-popup-content-item-unassign-all">
                      <a href="#" className="checkbox-link">
                        <span>Unassign</span>
                        <span className="unassign-all-icon"><span /></span>
                      </a>
                    </div>
                  </div>
                </div>
              </div>

              <div className="dpw--popup-content-line">
                <div className="dpmw--popup-content-of-three">
                  <h1 className="dpw--popup-item-collection-title">Agent <a href="#">Assign to me</a></h1>
                  <div className="dpw--popup-item-collection">
                    <div className="dpw--assignment-scrollable-container">
                      <Scrollable vertical />
                    </div>
                  </div>
                </div>

                <div className="dpmw--popup-content-of-three">
                  <h1 className="dpw--popup-item-collection-title">Team</h1>
                  <div className="dpw--popup-item-collection">
                    <div className="dpw--assignment-scrollable-container">
                      <Scrollable vertical />
                    </div>
                  </div>
                </div>

                <div className="dpmw--popup-content-of-three">
                  <h1 className="dpw--popup-item-collection-title">Department</h1>
                  <div className="dpw--popup-item-collection">
                    <div className="dpw--assignment-scrollable-container">
                      <Scrollable vertical />
                    </div>
                  </div>
                </div>
              </div>

              <div className="dpw--popup-content-line">
                <div className="dpw--popup-content-left">
                  <button type="submit" value="Save" className="dpw--popup-button">Save</button>
                </div>
              </div>

            </div>
          </form>
        </div>
      </div>
    );
  }
}
