import React, {Component, PropTypes} from 'react';

export class LabelsDictionary extends Component {

  static propTypes = {
    onClick: PropTypes.func.isRequired,
    labels: PropTypes.array.isRequired
  };

  groupByFirstLetter(labels) {
    const dictionary = {};
    // @ToDo some bug with length, size and count(). I can use only length at the moment.
    // but we have deprecation warning
    // https://github.com/facebook/immutable-js/issues/225
    for (let i = 0, label, letter; i < labels.length; i++) {
      label  = labels[i];
      letter = label[0].toUpperCase();
      if (!dictionary.hasOwnProperty(letter)) {
        dictionary[letter] = [];
      }

      dictionary[letter].push(label);
    }

    const letters = Object.keys(dictionary);
    const grouped = [];
    for (let i = 0; i < letters.length; i++) {
      grouped.push({letter: letters[i], labels: dictionary[letters[i]]});
    }

    return grouped;
  }

  render() {
    const grouped = this.groupByFirstLetter(this.props.labels);
    const onClick = this.props.onClick ? this.props.onClick : () => {
    };

    return (
      <section className="sidebar-list sidebar-list-labels tasks-nav-labels">
        <div className="sidebar-label-list sidebar-list">
          <span className="labelCharacter">--</span>
          <ul>
            <li onClick={onClick.bind(this, {name: 'no_labels', value: 1})}>
              <a href="#" className="item-label">no labels defined</a>
            </li>
          </ul>
          {grouped.map((group, index) => {
            return (
              <div key={index}>
                <span className="labelCharacter">{group.letter}</span>
                <ul>
                  {group.labels.map((label, index) =>
                    <li key={index} onClick={onClick.bind(this, {name: 'label', value: label})}>
                      <a href="#" className="item-label">{label}</a>
                    </li>)}
                </ul>
              </div>
            );
          })}
        </div>
      </section>
    );
  }

}
