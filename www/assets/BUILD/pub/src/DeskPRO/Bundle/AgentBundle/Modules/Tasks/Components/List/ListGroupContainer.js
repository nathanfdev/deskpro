import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import Immutable from 'immutable';
import { groupCollection } from 'Util/ListGroup';
import { currentOrderBySelector, elementsSelector } from '../../Selectors/list';
import { collectionSelectorFactory, allSelectorFactory } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore';
import { agentsSelector } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore/Shortcuts/agents';
import { editTask } from '../../Actions/listActions';

@connect(state => ({
  ids: elementsSelector(state),
  tasks: allSelectorFactory('Task')(state),
  orderBy: currentOrderBySelector(state),
  lists: allSelectorFactory('TaskList')(state),
  projects: allSelectorFactory('Project')(state),
  agents: agentsSelector(state),
  agentTeams: allSelectorFactory('AgentTeam')(state),
  departments: allSelectorFactory('Department')(state)
}))

export class ListGroupContainer extends React.Component {

  static propTypes = {
    dispatch: PropTypes.func.isRequired,
    orderBy: PropTypes.string,
    ids: PropTypes.object,
    tasks: PropTypes.object,
    lists: PropTypes.object.isRequired,
    projects: PropTypes.object.isRequired,
    agents: PropTypes.object.isRequired,
    agentTeams: PropTypes.object.isRequired,
    departments: PropTypes.object.isRequired,
    children: PropTypes.node.isRequired
  };

  componentWillReceiveProps() {
    console.time('ListGroupContainer did update');
  }

  componentDidUpdate() {
    console.timeEnd('ListGroupContainer did update');
  }

  onChangeGroup = (taskId, updateData) => {
    this.props.dispatch(editTask(taskId, updateData));
  };

  render() {
    const { orderBy, lists, projects, agents, agentTeams, departments } = this.props;
    const { ids, tasks, children } = this.props;
    const childProps = children.props;

    const groupConfig = {
      groupKey: orderBy,
      defaultGroupKey: 'list',
      options: {
        project: {
          type: 'record',
          records: projects,
          titleField: 'title',
          refField: 'project',
          emptyGroup: 'None'
        },
        date_due: {
          type: 'date',
          refField: 'date_due',
          dateGroupKeys: 'all'
        },
        date_done: {
          type: 'date',
          refField: 'date_done',
          dateGroupKeys: 'past'
        },
        date_created: {
          type: 'date',
          refField: 'date_created',
          dateGroupKeys: 'past'
        },
        assignee: {
          type: 'record',
          emptyGroup: 'None',
          collection: true,
          records: [
            {
              records: departments,
              titleField: 'title',
              refField: 'departments'
            },
            {
              records: agentTeams,
              titleField: 'name',
              refField: 'teams'
            },
            {
              records: agents,
              titleField: 'name',
              refField: 'agents'
            }
          ]
        },
        list: {
          type: 'record',
          records: lists,
          titleField: 'title',
          refField: 'list',
          emptyGroup: 'Tasks not in any list',
          sortBy: (a, b) => a.get('display_order') - b.get('display_order')
        }
      }
    };

    return React.cloneElement(children, {
      ...childProps,

      ids: ids,
      tasks: tasks,
      taskGroups: Immutable.fromJS(groupCollection(groupConfig, tasks)),
      onChangeGroup: this.onChangeGroup
    });
  }
}
