import React, {Component, PropTypes} from 'react';
import Immutable from 'immutable';

export class LabelsDictionary extends Component {

  static propTypes = {
    onClick: PropTypes.func.isRequired,
    labels: PropTypes.object.isRequired
  };

  groupByFirstLetter(labels) {
    const dictionary = {};
    let count;

    // count BC both for Immutable and JS objects
    count = labels.count() ? labels.count() : labels.length;
    for (let index = 0, label, letter; index < count; index++) {
      label = Immutable.Iterable.isIterable(labels) ? labels.get(index) : labels[index];
      letter = label.get('label')[0].toUpperCase();
      if (!dictionary.hasOwnProperty(letter)) {
        dictionary[letter] = [];
      }

      dictionary[letter].push(label);
    }

    const letters = Object.keys(dictionary);
    const grouped = [];

    // count BC both for Immutable and JS objects
    count = letters.count ? letters.count() : letters.length;
    for (let index = 0; index < count; index++) {
      grouped.push({ letter: letters[index], labels: dictionary[letters[index]] });
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
                  {group.labels.map((label, key) =>
                    <li key={key} onClick={onClick.bind(this, {name: 'label', value: label.get('label')})}>
                      <a href="#" className="item-label">{label.get('label')}</a>
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
