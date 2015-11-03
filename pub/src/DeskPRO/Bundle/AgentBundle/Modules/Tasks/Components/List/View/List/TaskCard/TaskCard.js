import React from 'react';
import { Card } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/Card';
import { CardLine } from './CardLine';
import { Title } from './Title';

export class TaskCard extends React.Component {

  constructor(props) {
    super(props);

    this.state = {
      expanded: false,
      title: 'Task title',
      isDone: true
    };
  }

  onTitleChange = value => {
    this.setState({
      title: value
    });
  };

  render() {
    return (
      <Card minimized={true} type="task">
        <div className="dpw--single-card-mark-done dpw--single-card-mark-done-minimized">
          <span>Done</span>
          <i className="fa fa-check"/>
        </div>

        <div className="dpw--single-card-mark-done">
          <i className="fa fa-check"/>
          <span>Mark Done</span>
        </div>

        <CardLine>
          <Title value={this.state.title}
                 isDone={this.state.isDone}
                 onChange={this.onTitleChange} />

          <div>
            <div className="dpw--card-expand">
              <a href="#">Detail button text <i className="fa fa-navicon"/></a>
            </div>
            <div className="dpwd--card-assigned">
              <div className="dpw--avatar-face" style={{position: 'relative'}}>
                <i className="fa fa-caret-down" />
              </div>
            </div>
          </div>
        </CardLine>

        <CardLine>
          <div>
            <span className="overdue dpwd--card-line-item">
              <i className="fa fa-calendar-o"/> Due: N/A
              <input type="text" name="due-date" className="due-date-field" disabled="disabled"/>
            </span>

            <span>
              <span className="dpw--card-disc"/>
              <span className="dpwd--card-line-item">
                <i className="fa fa-book"/> Some project
              </span>
            </span>

            <span>
              <span className="dpw--card-disc"/>
              <span className="dpwd--card-line-item">
                <i className="fa fa-link"/> <a href="#">Ticket title</a>
              </span>
            </span>
          </div>

          <div>
            <span className="dpwd--card-line-item">
              0 <i className="fa fa-comment"/>
            </span>

            <span className="dpwd--card-line-item">
              <div>
                <span className="dpw--card-disc"/> 1/3 <i className="fa fa-folder-open"/>
              </div>
            </span>
          </div>
        </CardLine>
      </Card>
    );
  }
}
