import React from 'react';

export class LabelsDictionary extends React.Component {
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
                    <li key={index} onClick={onClick.bind(this, {name:'label', value:label})}>
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

  groupByFirstLetter(labels) {
    const dictionary = {};

    for (let i = 0, label, letter; i < labels.size; i++) {
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
}
