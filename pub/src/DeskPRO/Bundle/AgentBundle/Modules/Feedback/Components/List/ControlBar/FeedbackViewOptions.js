import React, {Component, PropTypes} from 'react';
import Menu from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Menu/Menu';

export class FeedbackViewOptions extends Component {

  static propTypes = {

  };

  render() {
    return (
      <Menu widgetClass="dpw-navigation-dropdown-secondary">
          <li>
            <a href="#" className="dpw-navigation-dropdown-item">
              <span className="dpw-navigation-dropdown-item-mark">
                <span className="dpw-navigation-dropdown-item-disc"></span>
              </span>

              <span className="dpw-navigation-dropdown-item-title">List View</span>
            </a>
          </li>

          <li>
            <a href="#" className="dpw-navigation-dropdown-item">
              <span className="dpw-navigation-dropdown-item-mark">
                <span className="dpw-navigation-dropdown-item-disc dpw-navigation-dropdown-item-disc-active"></span>
              </span>
              <span className="dpw-navigation-dropdown-item-title">Date Created</span>
            </a>

            <div className="dpw-navigation-dropdown-column-list">
              <ul>
                <li>
                  <a className="dpw-navigation-dropdown-column-list-item" href="">
                    <span className="dpw-navigation-dropdown-column-list-status"><i className="fa fa-check"></i></span>
                    <span className="dpw-navigation-dropdown-column-list-move"><i className="fa fa-navicon"></i></span>
                    <span className="dpw-navigation-dropdown-column-list-title">Status</span>
                  </a>
                </li>

                <li>
                  <a className="dpw-navigation-dropdown-column-list-item" href="">
                    <span className="dpw-navigation-dropdown-column-list-status"><i className="fa fa-check"></i></span>
                    <span className="dpw-navigation-dropdown-column-list-move"><i className="fa fa-navicon"></i></span>
                    <span className="dpw-navigation-dropdown-column-list-title">Submitter</span>
                  </a>
                </li>

                <li>
                  <a className="dpw-navigation-dropdown-column-list-item" href="">
                    <span className="dpw-navigation-dropdown-column-list-status"><i className="fa fa-check"></i></span>
                    <span className="dpw-navigation-dropdown-column-list-move"><i className="fa fa-navicon"></i></span>
                    <span className="dpw-navigation-dropdown-column-list-title">Language</span>
                  </a>
                </li>

                <li>
                  <a className="dpw-navigation-dropdown-column-list-item" href="">
                    <span className="dpw-navigation-dropdown-column-list-status"><i className="fa fa-check"></i></span>
                    <span className="dpw-navigation-dropdown-column-list-move"><i className="fa fa-navicon"></i></span>
                    <span className="dpw-navigation-dropdown-column-list-title">Created Date</span>
                  </a>
                </li>

                <li>
                  <hr/>
                </li>

                <li>
                  <a className="dpw-navigation-dropdown-column-list-item disabled" href="">
                    <span className="dpw-navigation-dropdown-column-list-status"><i className="fa fa-check"></i></span>
                    <span className="dpw-navigation-dropdown-column-list-move"><i className="fa fa-minus"></i></span>
                    <span className="dpw-navigation-dropdown-column-list-title">ID</span>
                  </a>
                </li>


                <li>
                  <a className="dpw-navigation-dropdown-column-list-item disabled" href="">
                    <span className="dpw-navigation-dropdown-column-list-status"><i className="fa fa-check"></i></span>
                    <span className="dpw-navigation-dropdown-column-list-move"><i className="fa fa-minus"></i></span>
                    <span className="dpw-navigation-dropdown-column-list-title">Hidden Status</span>
                  </a>
                </li>

                <li>
                  <a className="dpw-navigation-dropdown-column-list-item disabled" href="">
                    <span className="dpw-navigation-dropdown-column-list-status"><i className="fa fa-check"></i></span>
                    <span className="dpw-navigation-dropdown-column-list-move"><i className="fa fa-minus"></i></span>
                    <span className="dpw-navigation-dropdown-column-list-title">Status Category</span>
                  </a>
                </li>

                <li>
                  <a className="dpw-navigation-dropdown-column-list-item disabled" href="">
                    <span className="dpw-navigation-dropdown-column-list-status"><i className="fa fa-check"></i></span>
                    <span className="dpw-navigation-dropdown-column-list-move"><i className="fa fa-minus"></i></span>
                    <span className="dpw-navigation-dropdown-column-list-title">Type</span>
                  </a>
                </li>

                <li>
                  <a className="dpw-navigation-dropdown-column-list-item disabled" href="">
                    <span className="dpw-navigation-dropdown-column-list-status"><i className="fa fa-check"></i></span>
                    <span className="dpw-navigation-dropdown-column-list-move"><i className="fa fa-minus"></i></span>
                    <span className="dpw-navigation-dropdown-column-list-title">Slug</span>
                  </a>
                </li>

              </ul>
            </div>
          </li>

          <li>
            <a href="#" className="dpw-navigation-dropdown-item">
              <span className="dpw-navigation-dropdown-item-mark">
                <span className="dpw-navigation-dropdown-item-disc"></span>
              </span>
              <span className="dpw-navigation-dropdown-item-title">Card View</span>
            </a>
          </li>

          <li>
            <a href="#" className="dpw-navigation-dropdown-item">
              <span className="dpw-navigation-dropdown-item-mark">
                <span className="dpw-navigation-dropdown-item-disc"></span>
              </span>
              <span className="dpw-navigation-dropdown-item-title">Calendar View</span>
            </a>
          </li>
        </Menu>
    );
  }
}