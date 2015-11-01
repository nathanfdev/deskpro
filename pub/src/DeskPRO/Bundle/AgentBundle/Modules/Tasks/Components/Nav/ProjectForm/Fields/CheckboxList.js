import React, { PropTypes } from 'react';
import { Scrollable } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Scrollable';

export class CheckboxList extends React.Component {

  static propTypes = {
    selected: PropTypes.array,
    options: PropTypes.array,
    onChange: PropTypes.func.isRequired
  };

  render() {
    return (
      <Scrollable vertical>
        <ul>
          <li>
            <a className="checkbox-button">
              <span className="checkbox"><i className="fa fa-check"></i></span>
              <span className="name">Agent 1</span>
            </a>
          </li>
          <li>
            <a className="checkbox-button">
              <span className="checkbox"><i className="fa fa-check"></i></span>
              <span className="name">Agent 2</span>
            </a>
          </li>
          <li>
            <a className="checkbox-button">
              <span className="checkbox"><i className="fa fa-check"></i></span>
              <span className="name">Agent 3</span>
            </a>
          </li>
          <li>
            <a className="checkbox-button checked">
              <span className="checkbox"><i className="fa fa-check"></i></span>
              <span className="name">Agent 4</span>
            </a>
          </li>
          <li>
            <a className="checkbox-button">
              <span className="checkbox"><i className="fa fa-check"></i></span>
              <span className="name">Agent 5</span>
            </a>
          </li>
          <li>
            <a className="checkbox-button">
              <span className="checkbox"><i className="fa fa-check"></i></span>
              <span className="name">Agent 6</span>
            </a>
          </li>
          <li>
            <a className="checkbox-button">
              <span className="checkbox"><i className="fa fa-check"></i></span>
              <span className="name">Agent 7</span>
            </a>
          </li>
          <li>
            <a className="checkbox-button">
              <span className="checkbox"><i className="fa fa-check"></i></span>
              <span className="name">Agent 8</span>
            </a>
          </li>
        </ul>
      </Scrollable>
    );
  }
}
