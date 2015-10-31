import React from 'react';
import TaskNavItemLabel from '../Components/TaskNavItemLabel';

export default class TasksNavLabels extends React.Component {
  static propTypes = {
    labelList: React.PropTypes.object
  }

  getCharacters(labels) {
    const sortedLabels = {};
    const sortedCharacters = [];
    labels.map((label) => {
      const lowerLabel = label.get('label').toLowerCase();
      const currentCharacter = lowerLabel.substr(0, 1).toUpperCase();

      if (sortedCharacters.indexOf(currentCharacter) === -1) {
        sortedCharacters.push(currentCharacter);
      }

      if (typeof sortedLabels[currentCharacter] === 'undefined') {
        sortedLabels[currentCharacter] = [];
      }

      sortedLabels[currentCharacter].push(label);
    });

    return {
      labels: sortedLabels,
      characters: sortedCharacters
    };
  }

  render() {
    const {labelList} = this.props;
    const _this = this;

    const labelMap = this.getCharacters(labelList);

    return (<section className="sidebar-list sidebar-list-labels tasks-nav-labels">
      <div className="list-sidebar-title">
        Labels
      </div>

      <div className="sidebar-label-list sidebar-list">
        <ul>{labelList && labelMap.characters ? labelMap.characters.map((character) => {
          const labels = labelMap.labels;

          return (<li key={character}>
            <span className="labelCharacter">{character}</span>
            {labels[character] ? labels[character].map((label) => {
              return <TaskNavItemLabel key={label.get('label')} label={label} filterTasks={_this.props.filterTasks.bind(_this)} />;
            }) : ''}
          </li>);
        }) : ''}</ul>
      </div>
    </section>);
  }
}
