import React from 'react';
import Formsy from 'formsy-react';
import FRC from '../../../../../Component/FormComponents/main.js';
import TaskCardGroup from '../Components/TaskCardGroup';

export default class ListView extends React.Component {
  static propTypes = {
    rawGroupings: React.PropTypes.array,
    groupedTasks: React.PropTypes.object
  }

  render() {
    return (<div>
      <Formsy.Form onSubmit={this.props.createTask.bind(this, source)}>
        <FRC.Input name="title" type="text"/>
        <button type="submit" value="Save" className="button">Add</button>
      </Formsy.Form>
      {this.props.rawGroupings ? this.props.rawGroupings.map((group) => {
        return (<TaskCardGroup tasks={this.props.groupedTasks[group.key]} key={group.id}
                               columnField={columnField}
                               source={source}
                               dispatch={this.props.dispatch}
                               updateField={group.updateField}
                               updateValue={group.updateValue}
                               teams={this.teams} projects={this.projects}
                               linked_items={this.props.linkedItems}
                               departments={this.departments} agents={this.agents}
                               tickets={this.props.tickets}
                               toggleDone={this.props.toggleDone}
                               editTask={this.props.editTask}
                               updateMassActions={this.props.updateMassActions}
                               actionable={this.props.state.actionable}
                               divider={group.title}
                               order={this.state.order}
                               toggleAssignWindow={this.props.toggleAssignWindow}
                               moveCard={this.moveCard}/>);
      }) : ''}
    </div>);
  }
}
