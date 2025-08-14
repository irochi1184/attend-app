import './bootstrap';
import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.data('attendanceApp', ({ initial, students, date }) => ({
  mode: 'present',
  modes: [
    { value: 'present', label: '出席' },
    { value: 'partial', label: '一部出席' },
  ],
  date,
  students,
  state: { ...initial }, // default は none
  clearMode: false, // ★追加：クリア送信フラグ

  clearAll() {
    this.state = {};
    this.clearMode = true; // ★ クリアしたことを覚えておく
  },

  toggle(userId) {
    const cur = this.state[userId];
    if (cur === this.mode) delete this.state[userId]; 
    else this.state[userId] = this.mode;
  },

  statusOf(id) { return this.state[id] ?? 'none'; },

  labelOf(st) {
    return ({
      present:'出席',
      partial:'一部出席',
      none:'-'
    })[st] ?? '-';
  },

  badgeClass(st) {
    return {
      present: 'bg-green-100 text-green-800',
      partial: 'bg-yellow-100 text-yellow-800',
      none:    'bg-gray-100 text-gray-600',
    }[st ?? 'none'];
  },

  markAll(val) { for (const s of this.students) this.state[s.id] = val; },
  clearAll() { this.state = {}; },
  selectedList() {
    return Object.entries(this.state)
      .map(([id, status]) => {
        let stu = this.students.find(s => s.id == id);
        return { id, name: stu?.name, status };
      })
      .filter(s => s.status !== '-'); // ★「-」は除外
  },
  serialize() {
    this.$refs.payload.innerHTML = '';

    // ★ state を hidden に展開（present/partial のみが入る）
    let i = 0;
    for (const [userId, status] of Object.entries(this.state)) {
      const uid = document.createElement('input');
      uid.type = 'hidden';
      uid.name = `entries[${i}][user_id]`;
      uid.value = userId;
      const st = document.createElement('input');
      st.type = 'hidden';
      st.name = `entries[${i}][status]`;
      st.value = status;
      this.$refs.payload.appendChild(uid);
      this.$refs.payload.appendChild(st);
      i++;
    }

    // ★ 何も選ばれていない（= クリアして確定）場合はフラグを送る
    if (this.clearMode || Object.keys(this.state).length === 0) {
      const wipe = document.createElement('input');
      wipe.type = 'hidden';
      wipe.name = 'wipe_all';
      wipe.value = '1';
      this.$refs.payload.appendChild(wipe);
    }
  },
}));

Alpine.start();
console.log('Alpine initialized: attendanceApp registered');
